<?php
// app/Http/Controllers/API/DailyActivityController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Daily Activity — Mobile API (for the assigned hse_staff).
 *
 * The daily_activity header is created/assigned by admin on the web. Here the
 * hse_staff:
 *   - sees the list of daily activities assigned to them,
 *   - inserts / updates / deletes the daily_activity_detail (to-do) items.
 *
 * Images (pictures_activity) are uploaded as multipart/form-data files.
 */
class DailyActivityController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/daily-activities — list daily activities assigned to me.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = DailyActivity::with([
            'user:id,name,department',
            'project:id,project_name,code',
            'location:id,name',
            'details.activity:id,name',
        ])
            ->withCount('details')
            ->where('user_id', $userId);

        if ($request->filled('date_from')) {
            $query->whereDate('datetime_activity', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('datetime_activity', '<=', $request->date_to);
        }
        if ($request->filled('month')) {
            try {
                [$year, $month] = explode('-', $request->month);
                $query->whereYear('datetime_activity', $year)->whereMonth('datetime_activity', $month);
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $query->orderBy('datetime_activity', 'desc');

        $items = $query->paginate($request->get('per_page', 10));
        $items->getCollection()->transform(fn($d) => $this->formatDaily($d));

        return $this->successResponse($items, 'Daily activities retrieved successfully');
    }

    /**
     * GET /api/daily-activities/{id} — show one assigned daily activity with details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $daily = DailyActivity::with([
            'user:id,name,department',
            'project:id,project_name,code',
            'location:id,name',
            'details.activity:id,name',
            'details.user:id,name',
        ])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$daily) {
            return $this->notFoundResponse('Daily activity not found or not assigned to you');
        }

        return $this->successResponse($this->formatDaily($daily, true), 'Daily activity retrieved successfully');
    }

    /**
     * GET /api/daily-activities/activities — active activity master list (for dropdown).
     */
    public function activities(): JsonResponse
    {
        $activities = Activity::active()->select('id', 'name', 'description')->orderBy('name')->get();
        return $this->successResponse($activities, 'Activities retrieved successfully');
    }

    /**
     * POST /api/daily-activities/{dailyActivityId}/details — insert a to-do detail.
     */
    public function storeDetail(Request $request, $dailyActivityId): JsonResponse
    {
        $userId = $request->user()->id;

        // The daily activity must be assigned to the authenticated user
        $daily = DailyActivity::where('user_id', $userId)->find($dailyActivityId);
        if (!$daily) {
            return $this->notFoundResponse('Daily activity not found or not assigned to you');
        }

        $validator = $this->validateDetail($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $picturePaths = $this->storeUploadedPictures($request->file('pictures_activity', []));

            $detail = DailyActivityDetail::create([
                'daily_activity_id'    => $daily->id,
                'activity_id'          => $request->activity_id,
                'todolist'             => $request->todolist,
                'activity_datetime'    => $request->activity_datetime,
                'status'               => $request->status,
                'description_status'   => $request->description_status,
                'pictures_activity'    => !empty($picturePaths) ? $picturePaths : null,
                'realization_datetime' => $request->realization_datetime,
                'user_id'              => $userId,
            ]);

            DB::commit();

            $detail->load('activity:id,name', 'user:id,name');

            return $this->successResponse($this->formatDetail($detail), 'Daily activity detail created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DailyActivityDetail store failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create detail', $e->getMessage(), 500);
        }
    }

    /**
     * PUT/POST /api/daily-activities/details/{detailId} — update a to-do detail.
     */
    public function updateDetail(Request $request, $detailId): JsonResponse
    {
        $detail = DailyActivityDetail::where('user_id', $request->user()->id)->find($detailId);
        if (!$detail) {
            return $this->notFoundResponse('Detail not found or not owned by you');
        }

        $validator = $this->validateDetail($request, true);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $current = is_array($detail->pictures_activity) ? $detail->pictures_activity : [];

            // existing_pictures[] = relative paths to KEEP (others get deleted)
            if ($request->has('existing_pictures')) {
                $keep = collect($request->input('existing_pictures', []))
                    ->map(fn($p) => $this->normalizePicturePath($p))
                    ->filter()->values()->toArray();

                foreach ($current as $path) {
                    if (!in_array($path, $keep, true)) {
                        $this->deletePicture($path);
                    }
                }
                $pictures = $keep;
            } else {
                $pictures = $current;
            }

            $pictures = array_merge($pictures, $this->storeUploadedPictures($request->file('pictures_activity', [])));

            $detail->update([
                'activity_id'          => $request->input('activity_id', $detail->activity_id),
                'todolist'             => $request->input('todolist', $detail->todolist),
                'activity_datetime'    => $request->input('activity_datetime', $detail->activity_datetime),
                'status'               => $request->input('status', $detail->status),
                'description_status'   => $request->input('description_status', $detail->description_status),
                'realization_datetime' => $request->input('realization_datetime', $detail->realization_datetime),
                'pictures_activity'    => !empty($pictures) ? array_values($pictures) : null,
            ]);

            DB::commit();

            $detail->load('activity:id,name', 'user:id,name');

            return $this->successResponse($this->formatDetail($detail), 'Daily activity detail updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DailyActivityDetail update failed', ['id' => $detailId, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update detail', $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/daily-activities/details/{detailId} — delete a to-do detail.
     */
    public function destroyDetail(Request $request, $detailId): JsonResponse
    {
        $detail = DailyActivityDetail::where('user_id', $request->user()->id)->find($detailId);
        if (!$detail) {
            return $this->notFoundResponse('Detail not found or not owned by you');
        }

        try {
            foreach ((is_array($detail->pictures_activity) ? $detail->pictures_activity : []) as $path) {
                $this->deletePicture($path);
            }
            $detail->delete();

            return $this->successResponse(null, 'Daily activity detail deleted successfully');
        } catch (\Exception $e) {
            Log::error('DailyActivityDetail delete failed', ['id' => $detailId, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete detail', $e->getMessage(), 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validateDetail(Request $request, bool $isUpdate = false): \Illuminate\Validation\Validator
    {
        $required = $isUpdate ? 'sometimes|required' : 'required';
        $statuses = implode(',', array_keys(DailyActivityDetail::STATUSES));

        return Validator::make($request->all(), [
            'activity_id'          => $required . '|exists:activities,id',
            'todolist'             => $required . '|string|max:5000',
            'activity_datetime'    => $required . '|date',
            'status'               => $required . '|in:' . $statuses,
            'description_status'   => 'nullable|string|max:5000',
            'realization_datetime' => 'nullable|date',
            'pictures_activity'    => 'nullable|array',
            'pictures_activity.*'  => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_pictures'    => 'nullable|array',
            'existing_pictures.*'  => 'string',
        ], [
            'activity_id.exists' => 'Selected activity does not exist',
            'status.in'          => 'Status must be one of: ' . $statuses,
            'pictures_activity.*.image' => 'Each picture must be an image file',
            'pictures_activity.*.max'   => 'Each picture may not be larger than 5MB',
        ]);
    }

    private function storeUploadedPictures($files): array
    {
        $paths = [];
        if (empty($files)) {
            return $paths;
        }

        foreach ((is_array($files) ? $files : [$files]) as $file) {
            try {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = 'daily_activity_' . time() . '_' . uniqid() . '.' . $ext;
                    $paths[] = $file->storeAs('daily_activity_images', $filename, 'public');
                }
            } catch (\Exception $e) {
                Log::warning('Failed to store daily activity picture', ['error' => $e->getMessage()]);
            }
        }

        return $paths;
    }

    private function normalizePicturePath(?string $path): ?string
    {
        if (!is_string($path) || $path === '') {
            return null;
        }
        if (str_contains($path, '/storage/')) {
            $path = substr($path, strpos($path, '/storage/') + strlen('/storage/'));
        }
        return ltrim($path, '/');
    }

    private function deletePicture(?string $path): void
    {
        if (is_string($path) && $path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function formatDaily(DailyActivity $daily, bool $withDetails = false): array
    {
        $data = [
            'id'                 => $daily->id,
            'datetime_activity'  => optional($daily->datetime_activity)->toIso8601String(),
            'personel'           => $daily->user,
            'project'            => $daily->project,
            'location'           => $daily->location,
            'description'        => $daily->description,
            'details_count'      => $daily->details_count ?? $daily->details()->count(),
        ];

        if ($withDetails) {
            $data['details'] = $daily->details->map(fn($d) => $this->formatDetail($d))->values();
        }

        return $data;
    }

    private function formatDetail(DailyActivityDetail $d): array
    {
        return [
            'id'                   => $d->id,
            'daily_activity_id'    => $d->daily_activity_id,
            'activity'             => $d->activity,
            'todolist'             => $d->todolist,
            'activity_datetime'    => optional($d->activity_datetime)->toIso8601String(),
            'status'               => $d->status,
            'status_label'         => $d->status_label,
            'description_status'   => $d->description_status,
            'realization_datetime' => optional($d->realization_datetime)->toIso8601String(),
            'pictures_activity'    => is_array($d->pictures_activity) ? $d->pictures_activity : [],
            'picture_urls'         => $d->picture_urls,
            'user_id'              => $d->user_id,
            'created_at'           => optional($d->created_at)->toIso8601String(),
            'updated_at'           => optional($d->updated_at)->toIso8601String(),
        ];
    }
}
