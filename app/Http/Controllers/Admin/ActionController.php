<?php
// app/Http/Controllers/Admin/ActionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Contributing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contributings = Contributing::active()->get();
        return view('admin.actions.index', compact('contributings'));
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        $actions = Action::with('contributing')->select(['id', 'contributing_id', 'name', 'description', 'is_active', 'created_at']);

        // Filter by contributing factor if provided
        if ($request->has('contributing_id') && $request->contributing_id != '') {
            $actions->where('contributing_id', $request->contributing_id);
        }

        return DataTables::of($actions)
            ->addIndexColumn()
            ->addColumn('contributing_name', function ($action) {
                return $action->contributing->name ?? '-';
            })
            ->addColumn('full_name', function ($action) {
                return $action->full_name ?? '-';
            })
            ->addColumn('description_short', function ($action) {
                return $action->description
                    ? (strlen($action->description) > 50
                        ? substr($action->description, 0, 50) . '...'
                        : $action->description)
                    : '-';
            })
            ->addColumn('status', function ($action) {
                $badgeClass = $action->is_active ? 'bg-success' : 'bg-danger';
                $badgeText = $action->is_active ? 'Active' : 'Inactive';
                return '<span class="badge ' . $badgeClass . '">' . $badgeText . '</span>';
            })
            ->addColumn('created_at_formatted', function ($action) {
                return $action->created_at->format('d M Y, H:i');
            })
            ->addColumn('action', function ($action) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewAction(' . $action->id . ')" title="View Details">';
                $actions .= '<i class="ri-eye-line"></i>';
                $actions .= '</button>';
                $actions .= '<button type="button" class="btn btn-sm btn-warning" onclick="editAction(' . $action->id . ')" title="Edit">';
                $actions .= '<i class="ri-edit-line"></i>';
                $actions .= '</button>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteAction(' . $action->id . ')" title="Delete">';
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
                'contributing_id' => 'required|exists:contributings,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'contributing_id.required' => 'Contributing factor is required.',
                'contributing_id.exists' => 'Selected contributing factor does not exist.',
                'name.required' => 'Action name is required.',
                'name.max' => 'Action name must not exceed 255 characters.',
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

            $action = Action::create([
                'contributing_id' => $request->contributing_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            $action->load('contributing');

            return response()->json([
                'success' => true,
                'message' => 'Action created successfully.',
                'data' => $action
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create action. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $action = Action::with('contributing')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action not found.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $action = Action::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'contributing_id' => 'required|exists:contributings,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'required|boolean'
            ], [
                'contributing_id.required' => 'Contributing factor is required.',
                'contributing_id.exists' => 'Selected contributing factor does not exist.',
                'name.required' => 'Action name is required.',
                'name.max' => 'Action name must not exceed 255 characters.',
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

            $action->update([
                'contributing_id' => $request->contributing_id,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => $request->is_active
            ]);

            $action->load('contributing');

            return response()->json([
                'success' => true,
                'message' => 'Action updated successfully.',
                'data' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update action. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $action = Action::findOrFail($id);
            $action->delete();

            return response()->json([
                'success' => true,
                'message' => 'Action deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete action. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle status of the specified resource.
     */
    public function toggleStatus($id)
    {
        try {
            $action = Action::findOrFail($id);
            $action->update(['is_active' => !$action->is_active]);

            $status = $action->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Action {$status} successfully.",
                'data' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.'
            ], 500);
        }
    }

    /**
     * Get actions by contributing factor
     */
    public function getByContributing($contributingId)
    {
        try {
            $actions = Action::where('contributing_id', $contributingId)
                ->active()
                ->select(['id', 'name'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load actions.'
            ], 500);
        }
    }

    /**
     * Get statistics for actions
     */
    public function getStatistics(Request $request)
    {
        try {
            $query = Action::query();

            // Filter by contributing factor if provided
            if ($request->has('contributing_id') && $request->contributing_id != '') {
                $query->where('contributing_id', $request->contributing_id);
            }

            $totalActions = $query->count();
            $activeActions = $query->where('is_active', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_actions' => $totalActions,
                    'active_actions' => $activeActions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics.'
            ], 500);
        }
    }

    /**
     * Get master data for dropdowns
     */
    public function getMasterData(): \Illuminate\Http\JsonResponse
    {
        try {
            $actions = Action::active()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
