<?php
// app/Http/Controllers/API/MasterDataController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Get all master data (categories with contributings and actions)
     */
    public function getAllMasterData()
    {
        $categories = Category::active()
            ->with([
                'contributings' => function ($query) {
                    $query->active()->with([
                        'actions' => function ($actionQuery) {
                            $actionQuery->active();
                        }
                    ]);
                }
            ])
            ->get();

        // Also provide flat structure for backward compatibility
        $contributings = Contributing::active()
            ->with([
                'category',
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
     * Get all categories with their contributing factors
     */
    public function getCategories()
    {
        $categories = Category::active()
            ->withCount('contributings')
            ->with([
                'contributings' => function ($query) {
                    $query->active()->withCount('actions');
                }
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Get all contributings with their actions and category
     */
    public function getContributings()
    {
        $contributings = Contributing::active()
            ->with([
                'category',
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
            ->with(['category', 'actions'])
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
     * Get full path for a specific action (category → contributing → action)
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
                'full_path' => $action->contributing->category->name . ' → ' . $action->contributing->name . ' → ' . $action->name
            ],
            'message' => 'Action path retrieved successfully'
        ]);
    }

    /**
     * Get specific contributing with its actions and category
     */
    public function getContributingDetail($contributingId)
    {
        $contributing = Contributing::active()
            ->with([
                'category',
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
     * Get contributing factors by category
     */
    public function getContributingsByCategory($categoryId)
    {
        $category = Category::active()->find($categoryId);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $contributings = $category->activeContributings()
            ->with([
                'actions' => function ($query) {
                    $query->active();
                }
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'contributings' => $contributings
            ],
            'message' => 'Contributing factors retrieved successfully'
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
            'categories_with_most_contributings' => Category::active()
                ->withCount('contributings')
                ->orderBy('contributings_count', 'desc')
                ->take(5)
                ->get(),
            'contributings_with_most_actions' => Contributing::active()
                ->with('category')
                ->withCount('actions')
                ->orderBy('actions_count', 'desc')
                ->take(5)
                ->get(),
            'categories_distribution' => Category::active()
                ->withCount('contributings')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'contributings_count' => $category->contributings_count,
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Master data statistics retrieved successfully'
        ]);
    }

    /**
     * Get all locations
     */
    public function getLocations()
    {
        $locations = Location::active()
            ->select('id', 'name', 'description', 'address', 'city', 'province', 'postal_code', 'latitude', 'longitude', 'created_at')
            ->orderBy('name')
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'description' => $location->description,
                    'address' => $location->address,
                    'city' => $location->city,
                    'province' => $location->province,
                    'postal_code' => $location->postal_code,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'full_address' => $location->full_address,
                    'coordinates' => $location->coordinates,
                    'has_coordinates' => $location->hasCoordinates(),
                    'created_at' => $location->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $locations,
            'message' => 'Locations retrieved successfully'
        ]);
    }

    /**
     * Get all users with employee role
     */
    public function getEmployeeUsers()
    {
        $employees = User::employees()
            ->active()
            ->select('id', 'name', 'email', 'department', 'phone', 'profile_image', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employees,
            'message' => 'Employee users retrieved successfully'
        ]);
    }
}
