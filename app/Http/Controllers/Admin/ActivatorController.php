<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ActivatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.activators.index');
    }

    /**
     * Get activators data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $activators = Activator::select(['id', 'name', 'description', 'is_active', 'created_at', 'updated_at']);

            return DataTables::of($activators)
                ->addIndexColumn()
                ->addColumn('status', function ($activator) {
                    $statusClass = $activator->is_active ? 'success' : 'danger';
                    $statusText = $activator->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('description_short', function ($activator) {
                    return $activator->description ? \Str::limit($activator->description, 50) : '-';
                })
                ->addColumn('created_at_formatted', function ($activator) {
                    return $activator->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($activator) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewActivator(' . $activator->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editActivator(' . $activator->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteActivator(' . $activator->id . ')" title="Delete">
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
                'message' => 'Failed to load activators: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:activators,name',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama activator wajib diisi',
                'name.unique' => 'Nama activator sudah ada',
                'name.max' => 'Nama activator maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $activator = Activator::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            Log::info('Activator created successfully', [
                'activator_id' => $activator->id,
                'name' => $activator->name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activator berhasil dibuat',
                'data' => $activator
            ], 201);
        } catch (\Exception $e) {
            Log::error('Activator creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat activator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $activator = Activator::find($id);

            if (!$activator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activator tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $activator
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $activator = Activator::find($id);

            if (!$activator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activator tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:activators,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama activator wajib diisi',
                'name.unique' => 'Nama activator sudah ada',
                'name.max' => 'Nama activator maksimal 255 karakter',
                'description.max' => 'Deskripsi maksimal 1000 karakter',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $activator->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            Log::info('Activator updated successfully', [
                'activator_id' => $activator->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activator berhasil diperbarui',
                'data' => $activator
            ]);
        } catch (\Exception $e) {
            Log::error('Activator update failed', [
                'activator_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui activator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $activator = Activator::find($id);

            if (!$activator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activator tidak ditemukan'
                ], 404);
            }

            $activatorName = $activator->name;
            $activator->delete();

            Log::info('Activator deleted successfully', [
                'activator_id' => $id,
                'activator_name' => $activatorName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activator berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Activator deletion failed', [
                'activator_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus activator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle activator status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $activator = Activator::find($id);

            if (!$activator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activator tidak ditemukan'
                ], 404);
            }

            $activator->update(['is_active' => !$activator->is_active]);

            $statusText = $activator->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Activator status toggled', [
                'activator_id' => $activator->id,
                'new_status' => $activator->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status activator berhasil ' . $statusText,
                'data' => [
                    'id' => $activator->id,
                    'is_active' => $activator->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}