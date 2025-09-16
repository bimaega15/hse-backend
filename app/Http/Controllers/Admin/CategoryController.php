<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.categories.index');
    }

    /**
     * Get categories data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $categories = Category::select(['id', 'name', 'description', 'is_active', 'created_at', 'updated_at']);

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('status', function ($category) {
                    $statusClass = $category->is_active ? 'success' : 'danger';
                    $statusText = $category->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('description_short', function ($category) {
                    return $category->description ? \Str::limit($category->description, 50) : '-';
                })
                ->addColumn('created_at_formatted', function ($category) {
                    return $category->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($category) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewCategory(' . $category->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editCategory(' . $category->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteCategory(' . $category->id . ')" title="Delete">
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
                'message' => 'Failed to load categories: ' . $e->getMessage()
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
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama kategori wajib diisi',
                'name.unique' => 'Nama kategori sudah ada',
                'name.max' => 'Nama kategori maksimal 255 karakter',
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

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'name' => $category->name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dibuat',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            Log::error('Category creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category
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
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama kategori wajib diisi',
                'name.unique' => 'Nama kategori sudah ada',
                'name.max' => 'Nama kategori maksimal 255 karakter',
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

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Category update failed', [
                'category_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            // Check if category is being used in reports or contributing factors
            $reportsCount = \DB::table('reports')->where('category_id', $id)->count();
            $contributingsCount = \DB::table('contributings')->where('category_id', $id)->count();

            if ($reportsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih digunakan dalam ' . $reportsCount . ' laporan'
                ], 400);
            }

            if ($contributingsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih memiliki ' . $contributingsCount . ' contributing factor'
                ], 400);
            }

            $categoryName = $category->name;
            $category->delete();

            Log::info('Category deleted successfully', [
                'category_id' => $id,
                'category_name' => $categoryName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Category deletion failed', [
                'category_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus kategori: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $category->update(['is_active' => !$category->is_active]);

            $statusText = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('Category status toggled', [
                'category_id' => $category->id,
                'new_status' => $category->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status kategori berhasil ' . $statusText,
                'data' => [
                    'id' => $category->id,
                    'is_active' => $category->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contributing factors by category
     */
    public function getContributings($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan'
                ], 404);
            }

            $contributings = $category->activeContributings()
                ->select(['id', 'name', 'description'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contributings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData(): \Illuminate\Http\JsonResponse
    {
        try {
            $categories = Category::active()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
