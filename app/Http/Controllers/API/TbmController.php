<?php
// app/Http/Controllers/API/TbmController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tbm;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * TBM / Safety Talk — Mobile API (full CRUD).
 *
 * Images (activity_pictures) are sent as multipart/form-data file uploads
 * under the `activity_pictures[]` field.
 */
class TbmController extends Controller
{
    use ApiResponseTrait;

    /**
     * GET /api/tbm — list TBM / Safety Talks (filter + pagination).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tbm::with([
            'speakerUser:id,name,email,department',
            'projectData:id,project_name,code',
            'locationData:id,name,city,province',
        ]);

        // Filter: date range
        if ($request->filled('date_from')) {
            $query->whereDate('date_time_tbm', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_time_tbm', '<=', $request->date_to);
        }

        // Filter: month (YYYY-MM)
        if ($request->filled('month')) {
            try {
                [$year, $month] = explode('-', $request->month);
                $query->whereYear('date_time_tbm', $year)->whereMonth('date_time_tbm', $month);
            } catch (\Throwable $e) {
                // ignore malformed month
            }
        }

        // Filter: speaker / project / location
        if ($request->filled('speaker')) {
            $query->where('speaker', $request->speaker);
        }
        if ($request->filled('project')) {
            $query->where('project', $request->project);
        }
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Search in summary topic
        if ($request->filled('search')) {
            $query->where('summary_topic', 'like', '%' . $request->search . '%');
        }

        $query->orderBy('date_time_tbm', 'desc');

        $tbms = $query->paginate($request->get('per_page', 10));

        $tbms->getCollection()->transform(fn($tbm) => $this->formatTbm($tbm));

        return $this->successResponse($tbms, 'TBM / Safety Talks retrieved successfully');
    }

    /**
     * POST /api/tbm — create a new TBM / Safety Talk.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateTbm($request);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $picturePaths = $this->storeUploadedPictures($request->file('activity_pictures', []));

            $tbm = Tbm::create([
                'date_time_tbm'     => $request->date_time_tbm,
                'speaker'           => $request->speaker,
                'project'           => $request->project,
                'location'          => $request->location,
                'participant_count' => $request->participant_count,
                'summary_topic'     => $request->summary_topic,
                'activity_pictures' => !empty($picturePaths) ? $picturePaths : null,
            ]);

            DB::commit();

            $tbm->load(['speakerUser:id,name,email,department', 'projectData:id,project_name,code', 'locationData:id,name,city,province']);

            return $this->successResponse($this->formatTbm($tbm), 'TBM / Safety Talk created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TBM store failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create TBM / Safety Talk', $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/tbm/{id} — show a single TBM / Safety Talk.
     */
    public function show($id): JsonResponse
    {
        $tbm = Tbm::with([
            'speakerUser:id,name,email,department',
            'projectData:id,project_name,code',
            'locationData:id,name,city,province',
        ])->find($id);

        if (!$tbm) {
            return $this->notFoundResponse('TBM / Safety Talk not found');
        }

        return $this->successResponse($this->formatTbm($tbm), 'TBM / Safety Talk retrieved successfully');
    }

    /**
     * PUT/POST /api/tbm/{id} — update a TBM / Safety Talk.
     *
     * Image handling:
     *  - `existing_pictures[]` (optional) = relative paths to KEEP. When sent,
     *    any current picture not in this list is deleted from storage.
     *    When omitted, current pictures are kept as-is.
     *  - `activity_pictures[]` (optional) = newly uploaded files, appended.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $tbm = Tbm::find($id);

        if (!$tbm) {
            return $this->notFoundResponse('TBM / Safety Talk not found');
        }

        $validator = $this->validateTbm($request, true);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            DB::beginTransaction();

            $current = is_array($tbm->activity_pictures) ? $tbm->activity_pictures : [];

            // Determine which existing pictures to keep
            if ($request->has('existing_pictures')) {
                $keep = collect($request->input('existing_pictures', []))
                    ->map(fn($p) => $this->normalizePicturePath($p))
                    ->filter()
                    ->values()
                    ->toArray();

                // Delete files that are no longer kept
                foreach ($current as $path) {
                    if (!in_array($path, $keep, true)) {
                        $this->deletePicture($path);
                    }
                }
                $pictures = $keep;
            } else {
                $pictures = $current;
            }

            // Append newly uploaded pictures
            $pictures = array_merge($pictures, $this->storeUploadedPictures($request->file('activity_pictures', [])));

            $tbm->update([
                'date_time_tbm'     => $request->input('date_time_tbm', $tbm->date_time_tbm),
                'speaker'           => $request->input('speaker', $tbm->speaker),
                'project'           => $request->input('project', $tbm->project),
                'location'          => $request->input('location', $tbm->location),
                'participant_count' => $request->input('participant_count', $tbm->participant_count),
                'summary_topic'     => $request->input('summary_topic', $tbm->summary_topic),
                'activity_pictures' => !empty($pictures) ? array_values($pictures) : null,
            ]);

            DB::commit();

            $tbm->load(['speakerUser:id,name,email,department', 'projectData:id,project_name,code', 'locationData:id,name,city,province']);

            return $this->successResponse($this->formatTbm($tbm), 'TBM / Safety Talk updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TBM update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update TBM / Safety Talk', $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/tbm/{id} — delete a TBM / Safety Talk.
     */
    public function destroy($id): JsonResponse
    {
        $tbm = Tbm::find($id);

        if (!$tbm) {
            return $this->notFoundResponse('TBM / Safety Talk not found');
        }

        try {
            // Remove associated picture files
            foreach ((is_array($tbm->activity_pictures) ? $tbm->activity_pictures : []) as $path) {
                $this->deletePicture($path);
            }

            $tbm->delete();

            return $this->successResponse(null, 'TBM / Safety Talk deleted successfully');
        } catch (\Exception $e) {
            Log::error('TBM delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete TBM / Safety Talk', $e->getMessage(), 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validateTbm(Request $request, bool $isUpdate = false): \Illuminate\Validation\Validator
    {
        $required = $isUpdate ? 'sometimes|required' : 'required';

        return Validator::make($request->all(), [
            'date_time_tbm'       => $required . '|date',
            'speaker'             => $required . '|exists:users,id',
            'project'             => $required . '|exists:projects,id',
            'location'            => $required . '|exists:locations,id',
            'participant_count'   => $required . '|integer|min:0',
            'summary_topic'       => 'nullable|string|max:5000',
            'activity_pictures'   => 'nullable|array',
            'activity_pictures.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_pictures'   => 'nullable|array',
            'existing_pictures.*' => 'string',
        ], [
            'speaker.exists'   => 'Selected speaker (user) does not exist',
            'project.exists'   => 'Selected project does not exist',
            'location.exists'  => 'Selected location does not exist',
            'activity_pictures.*.image' => 'Each activity picture must be an image file',
            'activity_pictures.*.max'   => 'Each activity picture may not be larger than 5MB',
        ]);
    }

    /**
     * Save uploaded image files to the public disk and return their paths.
     */
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
                    $filename = 'tbm_' . time() . '_' . uniqid() . '.' . $ext;
                    $paths[] = $file->storeAs('tbm_images', $filename, 'public');
                }
            } catch (\Exception $e) {
                Log::warning('Failed to store TBM picture', ['error' => $e->getMessage()]);
            }
        }

        return $paths;
    }

    /**
     * Normalize a (possibly full-URL) picture reference to its stored relative path.
     */
    private function normalizePicturePath(?string $path): ?string
    {
        if (!is_string($path) || $path === '') {
            return null;
        }

        // Strip the public storage URL prefix if a full URL was sent back
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

    /**
     * Shape a Tbm model into a clean API payload.
     */
    private function formatTbm(Tbm $tbm): array
    {
        return [
            'id'                    => $tbm->id,
            'date_time_tbm'         => optional($tbm->date_time_tbm)->toIso8601String(),
            'speaker'               => $tbm->speakerUser,
            'project'               => $tbm->projectData,
            'location'              => $tbm->locationData,
            'participant_count'     => (int) $tbm->participant_count,
            'summary_topic'         => $tbm->summary_topic,
            'activity_pictures'     => is_array($tbm->activity_pictures) ? $tbm->activity_pictures : [],
            'activity_picture_urls' => $tbm->activity_picture_urls,
            'created_at'            => optional($tbm->created_at)->toIso8601String(),
            'updated_at'            => optional($tbm->updated_at)->toIso8601String(),
        ];
    }
}
