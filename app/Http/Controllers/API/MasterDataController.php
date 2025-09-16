<?php
// app/Http/Controllers/API/MasterDataController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use App\Models\User;
use App\Models\Report;
use App\Models\Project;
use App\Models\Activator;
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
            'contributing_factors' => $this->getContributingFactorsAnalytics(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Master data statistics retrieved successfully'
        ]);
    }

    /**
     * Get contributing factors analytics from reports
     */
    private function getContributingFactorsAnalytics()
    {
        return Report::join('contributings', 'reports.contributing_id', '=', 'contributings.id')
            ->selectRaw('
                contributings.name as contributing,
                COUNT(*) as total,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open,
                AVG(CASE
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('contributings.id', 'contributings.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'contributing' => $item->contributing,
                    'total' => (int) $item->total,
                    'closed' => (int) $item->closed,
                    'open' => (int) $item->open,
                    'completion_rate' => $item->total > 0 ? round(($item->closed / $item->total) * 100, 1) : 0,
                    'avg_resolution_hours' => $item->avg_resolution_hours ? round($item->avg_resolution_hours, 1) : 0,
                ];
            });
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
     * Get all projects
     */
    public function getProjects()
    {
        $projects = Project::select('id', 'code', 'project_name', 'start_date', 'end_date', 'durasi', 'status', 'created_at')
            ->where('status', '!=', 'closed')
            ->orderBy('project_name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->code,
                    'project_name' => $project->project_name,
                    'name' => $project->project_name, // Alias for consistency with other master data
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'durasi' => $project->durasi,
                    'status' => $project->status,
                    'duration_in_days' => $project->duration_in_days,
                    'created_at' => $project->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $projects,
            'message' => 'Projects retrieved successfully'
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

    /**
     * Get all activators
     */
    public function getActivators()
    {
        $activators = Activator::active()
            ->select('id', 'name', 'description', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activators,
            'message' => 'Activators retrieved successfully'
        ]);
    }
}
