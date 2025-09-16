<?php
// app/Http/Controllers/API/ObservationController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\Category;
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
        $query = Observation::with(['user', 'details.category', 'details.activator']);

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
            'waktu_observasi' => 'required|date_format:H:i',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.activator_id' => 'nullable|exists:activators,id',
            'details.*.description' => 'required|string|max:2000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:1000',
        ]);

        // Custom validation for activator_id when observation_type is at_risk_behavior
        $validator->after(function ($validator) use ($request) {
            if ($request->has('details')) {
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

            // Create observation
            $observation = Observation::create([
                'user_id' => $request->user()->id,
                'waktu_observasi' => $request->waktu_observasi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'notes' => $request->notes,
                'status' => 'submitted',
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ]);

            // Create observation details and count each type
            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            foreach ($request->details as $detail) {
                ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $detail['observation_type'],
                    'category_id' => $detail['category_id'],
                    'activator_id' => $detail['activator_id'] ?? null,
                    'description' => $detail['description'],
                    'severity' => $detail['severity'],
                    'action_taken' => $detail['action_taken'] ?? null,
                ]);

                $counters[$detail['observation_type']]++;
            }

            // Update counters
            $observation->update($counters);

            DB::commit();

            // Load relationships for response
            $observation->load(['user', 'details.category']);

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

        $query = Observation::with(['user', 'details.category', 'details.activator']);

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
            'waktu_observasi' => 'sometimes|required|date_format:H:i',
            'waktu_mulai' => 'sometimes|required|date_format:H:i',
            'waktu_selesai' => 'sometimes|required|date_format:H:i|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'details' => 'sometimes|required|array|min:1',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.activator_id' => 'nullable|exists:activators,id',
            'details.*.description' => 'required|string|max:2000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:1000',
        ]);

        // Custom validation for activator_id when observation_type is at_risk_behavior
        $validator->after(function ($validator) use ($request) {
            if ($request->has('details')) {
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

            // Update observation basic info
            $observation->update($request->only([
                'waktu_observasi',
                'waktu_mulai',
                'waktu_selesai',
                'notes'
            ]));

            // If details are provided, recreate them
            if ($request->has('details')) {
                // Delete existing details
                $observation->details()->delete();

                // Create new details
                $counters = [
                    'at_risk_behavior' => 0,
                    'nearmiss_incident' => 0,
                    'informal_risk_mgmt' => 0,
                    'sim_k3' => 0,
                ];

                foreach ($request->details as $detail) {
                    ObservationDetail::create([
                        'observation_id' => $observation->id,
                        'observation_type' => $detail['observation_type'],
                        'category_id' => $detail['category_id'],
                        'activator_id' => $detail['activator_id'] ?? null,
                        'description' => $detail['description'],
                        'severity' => $detail['severity'],
                        'action_taken' => $detail['action_taken'] ?? null,
                    ]);

                    $counters[$detail['observation_type']]++;
                }

                // Update counters
                $observation->update($counters);
            }

            DB::commit();

            // Load relationships for response
            $observation->load(['user', 'details.category']);

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
            $observation->load(['user', 'details.category']);

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
            $observation->load(['user', 'details.category']);

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
