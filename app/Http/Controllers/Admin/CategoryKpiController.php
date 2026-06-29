<?php
// app/Http/Controllers/Admin/CategoryKpiController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryKpi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

/**
 * Category KPI master data — full CRUD.
 */
class CategoryKpiController extends Controller
{
    public function index(): View
    {
        return view('admin.kpi.categories.index');
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $categories = CategoryKpi::withCount('hseKpis')
                ->select(['id', 'category_name', 'status', 'created_at']);

            return DataTables::of($categories)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($c) {
                    $class = $c->status === 'active' ? 'success' : 'secondary';
                    return '<span class="badge bg-' . $class . '">' . ucwords($c->status) . '</span>';
                })
                ->addColumn('kpi_count', fn($c) => '<span class="badge bg-primary-subtle text-primary">' . $c->hse_kpis_count . ' KPI</span>')
                ->addColumn('created_at_formatted', fn($c) => $c->created_at->format('d M Y, H:i'))
                ->addColumn('action', function ($c) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editCategoryKpi(' . $c->id . ')" title="Edit"><i class="ri-edit-line"></i></button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteCategoryKpi(' . $c->id . ')" title="Delete"><i class="ri-delete-bin-line"></i></button>
                        </div>';
                })
                ->rawColumns(['status_badge', 'kpi_count', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function show($id): JsonResponse
    {
        $category = CategoryKpi::find($id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category KPI tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->save($request, $id);
    }

    private function save(Request $request, $id = null): JsonResponse
    {
        try {
            $unique = 'unique:category_kpi,category_name' . ($id ? ',' . $id : '');
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255|' . $unique,
                'status' => 'required|in:active,not active',
            ], [
                'category_name.required' => 'Nama kategori wajib diisi',
                'category_name.unique' => 'Nama kategori sudah ada',
                'status.required' => 'Status wajib dipilih',
                'status.in' => 'Status tidak valid',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
            }

            $data = $request->only('category_name', 'status');

            if ($id) {
                $category = CategoryKpi::find($id);
                if (!$category) {
                    return response()->json(['success' => false, 'message' => 'Category KPI tidak ditemukan'], 404);
                }
                $category->update($data);
                $message = 'Category KPI berhasil diperbarui';
            } else {
                $category = CategoryKpi::create($data);
                $message = 'Category KPI berhasil dibuat';
            }

            return response()->json(['success' => true, 'message' => $message, 'data' => $category], $id ? 200 : 201);
        } catch (\Exception $e) {
            Log::error('CategoryKpi save failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $category = CategoryKpi::find($id);
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Category KPI tidak ditemukan'], 404);
            }

            $usage = $category->hseKpis()->count();
            if ($usage > 0) {
                return response()->json(['success' => false, 'message' => 'Kategori tidak dapat dihapus karena masih digunakan pada ' . $usage . ' KPI'], 400);
            }

            $category->delete();
            return response()->json(['success' => true, 'message' => 'Category KPI berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('CategoryKpi delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
