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
     * Get all master data (categories separate, contributings with actions)
     */
    public function getAllMasterData()
    {
        $categories = Category::active()->get();

        $contributings = Contributing::active()
            ->with([
                'actions' => function ($query) {
                    $query->active();
                }
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'contributings' => $contributings
            ],
            'message' => 'Master data retrieved successfully'
        ]);
    }

    /**
     * Get all categories (standalone)
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
     * Get all contributings with their actions
     */
    public function getContributings()
    {
        $contributings = Contributing::active()
            ->with([
                'actions' => function ($query) {
                    $query->active();
                }
            ])
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
            ->with('contributing')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $actions,
            'message' => 'Actions retrieved successfully'
        ]);
    }

    /**
     * Get all actions
     */
    public function getActions()
    {
        $actions = Action::active()
            ->with('contributing')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $actions,
            'message' => 'All actions retrieved successfully'
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
            ->with('actions')
            ->get();

        $actions = Action::active()
            ->where('name', 'like', "%{$query}%")
            ->with('contributing')
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
     * Get full path for a specific action (contributing → action)
     */
    public function getActionPath($actionId)
    {
        $action = Action::with('contributing')->find($actionId);

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
                    'contributing' => $action->contributing,
                    'action' => $action
                ],
                'full_path' => $action->full_name
            ],
            'message' => 'Action path retrieved successfully'
        ]);
    }

    /**
     * Get specific contributing with its actions
     */
    public function getContributingDetail($contributingId)
    {
        $contributing = Contributing::active()
            ->with([
                'actions' => function ($query) {
                    $query->active();
                }
            ])
            ->find($contributingId);

        if (!$contributing) {
            return response()->json([
                'success' => false,
                'message' => 'Contributing not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contributing,
            'message' => 'Contributing details retrieved successfully'
        ]);
    }

    /**
     * Get statistics for master data
     */
    public function getStatistics()
    {
        $stats = [
            'total_categories' => Category::active()->count(),
            'total_contributings' => Contributing::active()->count(),
            'total_actions' => Action::active()->count(),
            'contributings_with_most_actions' => Contributing::active()
                ->withCount('actions')
                ->orderBy('actions_count', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Master data statistics retrieved successfully'
        ]);
    }
}
