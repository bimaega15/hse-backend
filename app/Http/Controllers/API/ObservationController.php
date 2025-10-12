<?php
// app/Http/Controllers/API/ObservationController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use App\Models\Project;
use App\Models\Activator;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ObservationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of observations with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Observation::with([
            'user',
            'project:id,project_name',
            'location:id,name',
            'details.category',
            'details.contributing',
            'details.action',
            'details.location',
            'details.project',
            'details.activator'
        ]);

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by observation type
        if ($request->has('observation_type') && $request->observation_type !== 'all') {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('observation_type', $request->observation_type);
            });
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('details', function ($detailQuery) use ($search) {
                        $detailQuery->where('description', 'like', "%{$search}%");
                    });
            });
        }

        // Sort by created_at desc by default
        $query->orderBy('created_at', 'desc');

        $observations = $query->paginate($request->get('per_page', 10));

        return $this->successResponse($observations, 'Observations retrieved successfully');
    }

    /**
     * Store a newly created observation
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'waktu_observasi' => 'required|date_format:H:i:s',
            'waktu_mulai' => 'required|date_format:H:i:s',
            'waktu_selesai' => 'required|date_format:H:i:s|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'project_id' => 'nullable|exists:projects,id',
            'details' => 'nullable|array',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.contributing_id' => 'required|exists:contributings,id',
            'details.*.action_id' => 'required|exists:actions,id',
            'details.*.location_id' => 'required|exists:locations,id',
            'details.*.project_id' => 'required|exists:projects,id',
            'details.*.activator_id' => 'nullable|exists:activators,id',
            'details.*.report_date' => 'required|date',
            'details.*.description' => 'required|string|max:2000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:1000',
            'details.*.images' => 'nullable|array',
            'details.*.images.*.name' => 'required_with:details.*.images.*|string',
            'details.*.images.*.type' => 'required_with:details.*.images.*|string',
            'details.*.images.*.size' => 'required_with:details.*.images.*|integer|max:2097152',
            'details.*.images.*.data' => 'required_with:details.*.images.*|string',
        ]);

        // Custom validation for activator_id when observation_type is at_risk_behavior
        $validator->after(function ($validator) use ($request) {
            if ($request->has('details') && !empty($request->details)) {
                foreach ($request->details as $index => $detail) {
                    if (isset($detail['observation_type']) && $detail['observation_type'] === 'at_risk_behavior') {
                        if (!isset($detail['activator_id']) || empty($detail['activator_id'])) {
                            $validator->errors()->add("details.{$index}.activator_id", 'Activator is required for At Risk Behavior observations.');
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            // Calculate duration in minutes
            $startTime = strtotime($request->waktu_mulai);
            $endTime = strtotime($request->waktu_selesai);
            $durationInMinutes = ($endTime - $startTime) / 60;

            // Create observation
            $observation = Observation::create([
                'user_id' => $request->user()->id,
                'waktu_observasi' => $request->waktu_observasi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'notes' => $request->notes,
                'location_id' => $request->location_id ?? null,
                'project_id' => $request->project_id ?? null,
                'status' => 'submitted',
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
                'total_observations' => 0,
            ]);

            // Create observation details and count each type
            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            // Only process details if array is not empty
            if (!empty($request->details)) {
                foreach ($request->details as $detail) {
                    // Process and save images as base64
                    $images = null;
                    if (isset($detail['images']) && is_array($detail['images'])) {
                        $imageArray = [];
                        foreach ($detail['images'] as $imageData) {
                            $imageArray[] = [
                                'name' => $imageData['name'],
                                'type' => $imageData['type'],
                                'size' => $imageData['size'],
                                'data' => $imageData['data']
                            ];
                        }
                        $images = json_encode($imageArray);
                    }

                    ObservationDetail::create([
                        'observation_id' => $observation->id,
                        'observation_type' => $detail['observation_type'],
                        'category_id' => $detail['category_id'],
                        'contributing_id' => $detail['contributing_id'],
                        'action_id' => $detail['action_id'],
                        'location_id' => $detail['location_id'],
                        'project_id' => $detail['project_id'] ?? null,
                        'activator_id' => $detail['activator_id'] ?? null,
                        'report_date' => $detail['report_date'],
                        'description' => $detail['description'],
                        'severity' => $detail['severity'],
                        'action_taken' => $detail['action_taken'] ?? null,
                        'images' => $images,
                    ]);

                    $counters[$detail['observation_type']]++;
                }
            }

            // Update counters and total observations
            $counters['total_observations'] = !empty($request->details) ? count($request->details) : 0;
            $observation->update($counters);

            DB::commit();

            // Load relationships for response
            $observation->load([
                'user',
                'project:id,project_name',
                'location:id,name',
                'details.category',
                'details.contributing',
                'details.action',
                'details.location',
                'details.project',
                'details.activator'
            ]);

            return $this->successResponse($observation, 'Observation created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create observation', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified observation
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $query = Observation::with([
            'user',
            'project:id,project_name',
            'location:id,name',
            'details.category',
            'details.contributing',
            'details.action',
            'details.location',
            'details.project',
            'details.activator'
        ]);

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $observation = $query->find($id);

        if (!$observation) {
            return $this->errorResponse('Observation not found', null, 404);
        }

        return $this->successResponse($observation, 'Observation retrieved successfully');
    }

    /**
     * Update the specified observation
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $query = Observation::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $observation = $query->find($id);

        if (!$observation) {
            return $this->errorResponse('Observation not found', null, 404);
        }

        if (!$observation->canBeEdited()) {
            return $this->errorResponse('Observation cannot be edited', 'Only draft observations can be edited', 403);
        }

        $validator = Validator::make($request->all(), [
            'waktu_observasi' => 'sometimes|required|date_format:H:i:s',
            'waktu_mulai' => 'sometimes|required|date_format:H:i:s',
            'waktu_selesai' => 'sometimes|required|date_format:H:i:s|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'location_id' => 'nullable|exists:locations,id',
            'project_id' => 'nullable|exists:projects,id',
            'details' => 'nullable|array',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.contributing_id' => 'required|exists:contributings,id',
            'details.*.action_id' => 'required|exists:actions,id',
            'details.*.location_id' => 'required|exists:locations,id',
            'details.*.project_id' => 'required|exists:projects,id',
            'details.*.activator_id' => 'nullable|exists:activators,id',
            'details.*.report_date' => 'required|date',
            'details.*.description' => 'required|string|max:2000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:1000',
            'details.*.images' => 'nullable|array',
        ]);

        // Custom validation for activator_id when observation_type is at_risk_behavior
        $validator->after(function ($validator) use ($request) {
            if ($request->has('details') && !empty($request->details)) {
                foreach ($request->details as $index => $detail) {
                    if (isset($detail['observation_type']) && $detail['observation_type'] === 'at_risk_behavior') {
                        if (!isset($detail['activator_id']) || empty($detail['activator_id'])) {
                            $validator->errors()->add("details.{$index}.activator_id", 'Activator is required for At Risk Behavior observations.');
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            // Update observation basic info including location and project
            $updateData = $request->only([
                'waktu_observasi',
                'waktu_mulai',
                'waktu_selesai',
                'notes',
                'location_id',
                'project_id'
            ]);

            // Update observation basic info
            $observation->update($updateData);

            // Delete existing details and recreate
            $observation->details()->delete();

            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            // Only process details if array is not empty
            if (!empty($request->details)) {
                foreach ($request->details as $detail) {
                    // Process and save images as base64
                    $images = null;
                    if (isset($detail['images']) && is_array($detail['images'])) {
                        $imageArray = [];
                        foreach ($detail['images'] as $imageData) {
                            $imageArray[] = [
                                'name' => $imageData['name'],
                                'type' => $imageData['type'],
                                'size' => $imageData['size'],
                                'data' => $imageData['data']
                            ];
                        }
                        $images = json_encode($imageArray);
                    }

                    ObservationDetail::create([
                        'observation_id' => $observation->id,
                        'observation_type' => $detail['observation_type'],
                        'category_id' => $detail['category_id'],
                        'contributing_id' => $detail['contributing_id'],
                        'action_id' => $detail['action_id'],
                        'location_id' => $detail['location_id'],
                        'project_id' => $detail['project_id'] ?? null,
                        'activator_id' => $detail['activator_id'] ?? null,
                        'report_date' => $detail['report_date'],
                        'description' => $detail['description'],
                        'severity' => $detail['severity'],
                        'action_taken' => $detail['action_taken'] ?? null,
                        'images' => $images,
                    ]);

                    $counters[$detail['observation_type']]++;
                }
            }

            // Update counters and total observations
            $counters['total_observations'] = !empty($request->details) ? count($request->details) : 0;
            $observation->update($counters);

            DB::commit();

            // Load relationships for response
            $observation->load([
                'user',
                'project:id,project_name',
                'location:id,name',
                'details.category',
                'details.contributing',
                'details.action',
                'details.location',
                'details.project',
                'details.activator'
            ]);

            return $this->successResponse($observation, 'Observation updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update observation', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified observation
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $query = Observation::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $observation = $query->find($id);

        if (!$observation) {
            return $this->errorResponse('Observation not found', null, 404);
        }

        if (!$observation->canBeEdited()) {
            return $this->errorResponse('Observation cannot be deleted', 'Only draft observations can be deleted', 403);
        }

        try {
            $observation->delete();
            return $this->successResponse(null, 'Observation deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete observation', $e->getMessage(), 500);
        }
    }

    /**
     * Submit observation for review
     */
    public function submit(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $query = Observation::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $observation = $query->find($id);

        if (!$observation) {
            return $this->errorResponse('Observation not found', null, 404);
        }

        if (!$observation->canBeSubmitted()) {
            return $this->errorResponse('Observation cannot be submitted', 'Observation must be in draft status and have at least one detail', 403);
        }

        try {
            $observation->update(['status' => 'submitted']);
            $observation->load([
                'user',
                'project:id,project_name',
                'location:id,name',
                'details.category',
                'details.contributing',
                'details.action',
                'details.location',
                'details.project',
                'details.activator'
            ]);

            return $this->successResponse($observation, 'Observation submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit observation', $e->getMessage(), 500);
        }
    }

    /**
     * Mark observation as reviewed (HSE staff only)
     */
    public function review(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'hse_staff') {
            return $this->errorResponse('Unauthorized', 'Only HSE staff can review observations', 403);
        }

        $observation = Observation::find($id);

        if (!$observation) {
            return $this->errorResponse('Observation not found', null, 404);
        }

        if ($observation->status !== 'submitted') {
            return $this->errorResponse('Invalid status', 'Only submitted observations can be reviewed', 403);
        }

        try {
            $observation->update(['status' => 'reviewed']);
            $observation->load([
                'user',
                'project:id,project_name',
                'location:id,name',
                'details.category',
                'details.contributing',
                'details.action',
                'details.location',
                'details.project',
                'details.activator'
            ]);

            return $this->successResponse($observation, 'Observation reviewed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to review observation', $e->getMessage(), 500);
        }
    }

    /**
     * Get observation statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Observation::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $totalObservations = $query->count();
        $draftObservations = (clone $query)->where('status', 'draft')->count();
        $submittedObservations = (clone $query)->where('status', 'submitted')->count();
        $reviewedObservations = (clone $query)->where('status', 'reviewed')->count();

        // Type statistics
        $typeStats = [
            'at_risk_behavior' => (clone $query)->sum('at_risk_behavior'),
            'nearmiss_incident' => (clone $query)->sum('nearmiss_incident'),
            'informal_risk_mgmt' => (clone $query)->sum('informal_risk_mgmt'),
            'sim_k3' => (clone $query)->sum('sim_k3'),
        ];

        // Severity statistics
        $severityStats = ObservationDetail::query()
            ->whereHas('observation', function ($q) use ($user) {
                if ($user->role === 'employee') {
                    $q->where('user_id', $user->id);
                }
            })
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Today's statistics
        $todayStats = [
            'total' => (clone $query)->whereDate('created_at', today())->count(),
            'submitted' => (clone $query)->whereDate('created_at', today())->where('status', 'submitted')->count(),
        ];

        return $this->successResponse([
            'total_observations' => $totalObservations,
            'draft_observations' => $draftObservations,
            'submitted_observations' => $submittedObservations,
            'reviewed_observations' => $reviewedObservations,
            'completion_rate' => $totalObservations > 0 ? round(($reviewedObservations / $totalObservations) * 100, 1) : 0,
            'type_statistics' => $typeStats,
            'severity_statistics' => $severityStats,
            'today_statistics' => $todayStats,
        ], 'Statistics retrieved successfully');
    }

    /**
     * Get observation dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // Recent observations
        $recentQuery = Observation::with(['user', 'details'])
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if ($user->role === 'employee') {
            $recentQuery->where('user_id', $user->id);
        }

        $recentObservations = $recentQuery->get();

        // Get statistics
        $statisticsResponse = $this->statistics($request);
        $statistics = $statisticsResponse->getData()->data;

        return $this->successResponse([
            'recent_observations' => $recentObservations,
            'statistics' => $statistics,
        ], 'Dashboard data retrieved successfully');
    }
}
