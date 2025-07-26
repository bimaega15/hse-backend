<?php
// app/Http/Controllers/API/MasterDataController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Get all categories with their contributings and actions (hierarchical)
     */
    public function getHierarchicalData()
    {
        $categories = Category::active()
            ->with([
                'contributings' => function ($query) {
                    $query->active()->with([
                        'actions' => function ($subQuery) {
                            $subQuery->active();
                        }
                    ]);
                }
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Master data retrieved successfully'
        ]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = Category::active()->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Get contributings by category
     */
    public function getContributingsByCategory($categoryId)
    {
        $contributings = Contributing::active()
            ->where('category_id', $categoryId)
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contributings,
            'message' => 'Contributings retrieved successfully'
        ]);
    }

    /**
     * Get actions by contributing
     */
    public function getActionsByContributing($contributingId)
    {
        $actions = Action::active()
            ->where('contributing_id', $contributingId)
            ->with(['contributing.category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $actions,
            'message' => 'Actions retrieved successfully'
        ]);
    }

    /**
     * Get actions by category (all actions under a category)
     */
    public function getActionsByCategory($categoryId)
    {
        $actions = Action::active()
            ->whereHas('contributing', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with(['contributing.category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $actions,
            'message' => 'Actions by category retrieved successfully'
        ]);
    }

    /**
     * Search across all master data
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $categories = Category::active()
            ->where('name', 'like', "%{$query}%")
            ->get();

        $contributings = Contributing::active()
            ->where('name', 'like', "%{$query}%")
            ->with('category')
            ->get();

        $actions = Action::active()
            ->where('name', 'like', "%{$query}%")
            ->with(['contributing.category'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'contributings' => $contributings,
                'actions' => $actions
            ],
            'message' => 'Search results retrieved successfully'
        ]);
    }

    /**
     * Get full path for a specific action
     */
    public function getActionPath($actionId)
    {
        $action = Action::with(['contributing.category'])->find($actionId);

        if (!$action) {
            return response()->json([
                'success' => false,
                'message' => 'Action not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'action' => $action,
                'path' => [
                    'category' => $action->contributing->category,
                    'contributing' => $action->contributing,
                    'action' => $action
                ],
                'full_path' => $action->full_name
            ],
            'message' => 'Action path retrieved successfully'
        ]);
    }
}
