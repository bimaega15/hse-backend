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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        // Filter by project
        if ($request->has('project_id') && $request->project_id !== 'all') {
            $query->where('project_id', $request->project_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('details', function ($detailQuery) use ($search) {
                        $detailQuery->where('description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('project', function ($q) use ($search) {
                        $q->where('project_name', 'like', "%{$search}%");
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
            'waktu_selesai' => 'required|date_format:H:i:s|after_or_equal:waktu_mulai',
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
            'details.*.images.*.size' => 'required_with:details.*.images.*|numeric|max:10485760',
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
                    // Process and save images to disk storage
                    $imagePaths = $this->handleObservationImages($detail['images'] ?? []);

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
                        'images' => !empty($imagePaths) ? $imagePaths : null,
                    ]);

                    $counters[$detail['observation_type']]++;
                }
            }

            // Update counters and total observations
            $counters['total_observations'] = !empty($request->details) ? count($request->details) : 0;

            // Auto-fill location_id and project_id from first detail if not provided
            if (empty($observation->location_id) && !empty($request->details)) {
                $firstDetail = $request->details[0];
                $counters['location_id'] = $firstDetail['location_id'] ?? null;
            }
            if (empty($observation->project_id) && !empty($request->details)) {
                $firstDetail = $request->details[0];
                $counters['project_id'] = $firstDetail['project_id'] ?? null;
            }

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
            'waktu_selesai' => 'sometimes|required|date_format:H:i:s|after_or_equal:waktu_mulai',
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

            // Delete existing details (and their image files) then recreate
            foreach ($observation->details as $existingDetail) {
                $this->deleteObservationDetailImages($existingDetail);
            }
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
                    // Process and save images to disk storage
                    $imagePaths = $this->handleObservationImages($detail['images'] ?? []);

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
                        'images' => !empty($imagePaths) ? $imagePaths : null,
                    ]);

                    $counters[$detail['observation_type']]++;
                }
            }

            // Update counters and total observations
            $counters['total_observations'] = !empty($request->details) ? count($request->details) : 0;

            // Auto-fill location_id and project_id from first detail if not provided
            if (empty($observation->location_id) && !empty($request->details)) {
                $firstDetail = $request->details[0];
                $counters['location_id'] = $firstDetail['location_id'] ?? null;
            }
            if (empty($observation->project_id) && !empty($request->details)) {
                $firstDetail = $request->details[0];
                $counters['project_id'] = $firstDetail['project_id'] ?? null;
            }

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
            // Delete image files from storage before deleting observation
            foreach ($observation->details as $detail) {
                $this->deleteObservationDetailImages($detail);
            }

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

    /**
     * Handle observation images - save base64 images to disk storage
     */
    private function handleObservationImages(array $images): array
    {
        $imagePaths = [];

        if (empty($images)) {
            return $imagePaths;
        }

        foreach ($images as $index => $imageData) {
            try {
                if (is_string($imageData)) {
                    // Handle plain base64 string
                    $path = $this->saveObservationBase64Image($imageData);
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                } elseif (is_array($imageData) && isset($imageData['data'])) {
                    // Handle object with {name, type, size, data} format from frontend
                    $path = $this->saveObservationBase64Image($imageData['data'], $imageData['name'] ?? null);
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                } elseif ($imageData instanceof \Illuminate\Http\UploadedFile) {
                    // Handle file upload
                    if ($imageData->isValid()) {
                        $path = $this->saveObservationUploadedFile($imageData);
                        if ($path) {
                            $imagePaths[] = $path;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process observation image', [
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $imagePaths;
    }

    /**
     * Save base64 image to observation_images storage
     */
    private function saveObservationBase64Image(string $base64Data, ?string $originalName = null): ?string
    {
        try {
            // Remove data:image/...;base64, prefix if present
            if (strpos($base64Data, ',') !== false) {
                $base64Data = explode(',', $base64Data)[1];
            }

            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                throw new \Exception('Invalid base64 data');
            }

            // Validate image
            $imageInfo = getimagesizefromstring($imageData);
            if ($imageInfo === false) {
                throw new \Exception('Invalid image data');
            }

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/jpg' => 'jpg',
            ];

            if (!isset($allowedMimes[$imageInfo['mime']])) {
                throw new \Exception('Unsupported image type: ' . $imageInfo['mime']);
            }

            // Check file size (max 5MB)
            if (strlen($imageData) > 5242880) {
                throw new \Exception('Image exceeds maximum size of 5MB');
            }

            $extension = $allowedMimes[$imageInfo['mime']];
            $filename = 'observation_' . time() . '_' . uniqid() . '.' . $extension;
            $path = 'observation_images/' . $filename;

            if (Storage::disk('public')->put($path, $imageData)) {
                return $path;
            }

            throw new \Exception('Failed to save image to storage');
        } catch (\Exception $e) {
            Log::error('Failed to save observation base64 image', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Save uploaded file to observation_images storage
     */
    private function saveObservationUploadedFile(\Illuminate\Http\UploadedFile $file): ?string
    {
        try {
            $filename = 'observation_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            return $file->storeAs('observation_images', $filename, 'public');
        } catch (\Exception $e) {
            Log::error('Failed to save observation uploaded file', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete image files for a specific observation detail
     */
    private function deleteObservationDetailImages(ObservationDetail $detail): void
    {
        $images = $detail->images;

        if (empty($images)) {
            return;
        }

        foreach ($images as $imagePath) {
            if (is_string($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }
    }
}
