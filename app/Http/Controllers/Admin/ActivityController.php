<?php
// app/Http/Controllers/Admin/ActivityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

/**
 * Activity master data (Activity Data menu) — full CRUD.
 */
class ActivityController extends Controller
{
    public function index(): View
    {
        return view('admin.activities.index');
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $activities = Activity::select(['id', 'name', 'description', 'is_active', 'created_at', 'updated_at']);

            return DataTables::of($activities)
                ->addIndexColumn()
                ->addColumn('status', function ($activity) {
                    $statusClass = $activity->is_active ? 'success' : 'danger';
                    $statusText = $activity->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('description_short', function ($activity) {
                    return $activity->description ? \Str::limit($activity->description, 60) : '-';
                })
                ->addColumn('created_at_formatted', function ($activity) {
                    return $activity->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($activity) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewActivity(' . $activity->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editActivity(' . $activity->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteActivity(' . $activity->id . ')" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activities: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:activities,name',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'Nama activity wajib diisi',
                'name.unique' => 'Nama activity sudah ada',
                'name.max' => 'Nama activity maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'is_active.required' => 'Status wajib dipilih',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $activity = Activity::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            Log::info('Activity created', ['activity_id' => $activity->id, 'created_by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Activity berhasil dibuat',
                'data' => $activity,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Activity creation failed', ['error' => $e->getMessage(), 'user_id' => auth()->id()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat activity: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Activity tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $activity]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json(['success' => false, 'message' => 'Activity tidak ditemukan'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:activities,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean',
            ], [
                'name.required' => 'Nama activity wajib diisi',
                'name.unique' => 'Nama activity sudah ada',
                'name.max' => 'Nama activity maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'is_active.required' => 'Status wajib dipilih',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $activity->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active,
            ]);

            Log::info('Activity updated', ['activity_id' => $activity->id, 'updated_by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Activity berhasil diperbarui',
                'data' => $activity,
            ]);
        } catch (\Exception $e) {
            Log::error('Activity update failed', ['activity_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui activity: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json(['success' => false, 'message' => 'Activity tidak ditemukan'], 404);
            }

            // Prevent delete if still used by daily activity details
            $usage = $activity->dailyActivityDetails()->count();
            if ($usage > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity tidak dapat dihapus karena masih digunakan pada ' . $usage . ' daily activity detail',
                ], 400);
            }

            $name = $activity->name;
            $activity->delete();

            Log::info('Activity deleted', ['activity_id' => $id, 'name' => $name, 'deleted_by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Activity berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Activity deletion failed', ['activity_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus activity: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus($id): JsonResponse
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json(['success' => false, 'message' => 'Activity tidak ditemukan'], 404);
            }

            $activity->update(['is_active' => !$activity->is_active]);
            $statusText = $activity->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'success' => true,
                'message' => 'Status activity berhasil ' . $statusText,
                'data' => ['id' => $activity->id, 'is_active' => $activity->is_active],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Master data for dropdowns (web + api reuse).
     */
    public function getMasterData(): JsonResponse
    {
        try {
            $activities = Activity::active()->select(['id', 'name'])->orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $activities]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
