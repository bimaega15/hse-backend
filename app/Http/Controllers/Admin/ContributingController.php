<?php
// app/Http/Controllers/Admin/ContributingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contributing;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ContributingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.contributing.index', compact('categories'));
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $contributings = Contributing::with(['actions', 'category'])->select(['id', 'category_id', 'name', 'description', 'is_active', 'created_at']);

        return DataTables::of($contributings)
            ->addIndexColumn()
            ->addColumn('description_short', function ($contributing) {
                return $contributing->description
                    ? (strlen($contributing->description) > 50
                        ? substr($contributing->description, 0, 50) . '...'
                        : $contributing->description)
                    : '-';
            })
            ->addColumn('actions_count', function ($contributing) {
                return $contributing->actions_count ?? 0;
            })
            ->addColumn('active_actions_count', function ($contributing) {
                return $contributing->active_actions_count ?? 0;
            })
            ->addColumn('category_name', function ($contributing) {
                return $contributing->category ? $contributing->category->name : '-';
            })
            ->addColumn('status', function ($contributing) {
                $badgeClass = $contributing->is_active ? 'bg-success' : 'bg-danger';
                $badgeText = $contributing->is_active ? 'Active' : 'Inactive';
                return '<span class="badge ' . $badgeClass . '">' . $badgeText . '</span>';
            })
            ->addColumn('created_at_formatted', function ($contributing) {
                return $contributing->created_at->format('d M Y, H:i');
            })
            ->addColumn('action', function ($contributing) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewContributing(' . $contributing->id . ')" title="View Details">';
                $actions .= '<i class="ri-eye-line"></i>';
                $actions .= '</button>';
                $actions .= '<button type="button" class="btn btn-sm btn-warning" onclick="editContributing(' . $contributing->id . ')" title="Edit">';
                $actions .= '<i class="ri-edit-line"></i>';
                $actions .= '</button>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteContributing(' . $contributing->id . ')" title="Delete">';
                $actions .= '<i class="ri-delete-bin-line"></i>';
                $actions .= '</button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255|unique:contributings,name',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'category_id.required' => 'Category is required.',
                'category_id.exists' => 'Selected category is invalid.',
                'name.required' => 'Contributing factor name is required.',
                'name.unique' => 'This contributing factor name already exists.',
                'name.max' => 'Contributing factor name must not exceed 255 characters.',
                'description.max' => 'Description must not exceed 1000 characters.',
                'is_active.required' => 'Status is required.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contributing = Contributing::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contributing factor created successfully.',
                'data' => $contributing
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create contributing factor. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $contributing = Contributing::with(['actions', 'category'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $contributing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Contributing factor not found.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $contributing = Contributing::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255|unique:contributings,name,' . $id,
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'category_id.required' => 'Category is required.',
                'category_id.exists' => 'Selected category is invalid.',
                'name.required' => 'Contributing factor name is required.',
                'name.unique' => 'This contributing factor name already exists.',
                'name.max' => 'Contributing factor name must not exceed 255 characters.',
                'description.max' => 'Description must not exceed 1000 characters.',
                'is_active.required' => 'Status is required.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $contributing->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contributing factor updated successfully.',
                'data' => $contributing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contributing factor. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $contributing = Contributing::findOrFail($id);

            // Check if contributing factor has related actions
            if ($contributing->actions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this contributing factor because it has related actions.'
                ], 400);
            }

            $contributing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contributing factor deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contributing factor. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle status of the specified resource.
     */
    public function toggleStatus($id)
    {
        try {
            $contributing = Contributing::findOrFail($id);
            $contributing->update(['is_active' => !$contributing->is_active]);

            $status = $contributing->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Contributing factor {$status} successfully.",
                'data' => $contributing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.'
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData(): \Illuminate\Http\JsonResponse
    {
        try {
            $contributings = Contributing::active()
                ->select(['id', 'name'])
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
}
