<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        // Determine view type - default, submitted, or analytics
        $view = $request->get('view', 'default');
        $status = $request->get('status');

        // Pass additional data for analytics view
        $additionalData = [];

        if ($view === 'analytics') {
            $additionalData = $this->getAnalyticsData();
        }

        return view('admin.observations.index', compact('view', 'status', 'additionalData'));
    }

    public function getData(Request $request)
    {
        try {
            Log::info('Observations DataTables Request Parameters:', $request->all());

            $query = Observation::with([
                'user:id,name,email,department',
                'details.category:id,name'
            ]);

            // Apply filters with validation
            if ($request->filled('status') && in_array($request->status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->status);
            }

            if ($request->filled('observation_type')) {
                $query->whereHas('details', function ($q) use ($request) {
                    $q->where('observation_type', $request->observation_type);
                });
            }

            if ($request->filled('start_date') && strtotime($request->start_date)) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date') && strtotime($request->end_date)) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // NEW: Handle URL filters
            if ($request->filled('url_status') && in_array($request->url_status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->url_status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('observer_info', function ($observation) {
                    try {
                        $observerName = optional($observation->user)->name ?? 'N/A';
                        $department = optional($observation->user)->department ?? 'N/A';
                        $userId = $observation->user_id ?? 'N/A';
                        return "<div class='fw-bold'>{$observerName}</div><small class='text-muted'>Dept: {$department} | ID: {$userId}</small>";
                    } catch (\Exception $e) {
                        Log::error('Error in observer_info column: ' . $e->getMessage());
                        return 'Error loading observer';
                    }
                })
                ->addColumn('observation_summary', function ($observation) {
                    try {
                        $total = $observation->total_observations ?? 0;
                        $time = $observation->waktu_observasi ? date('H:i', strtotime($observation->waktu_observasi)) : 'N/A';
                        $duration = $observation->duration_in_minutes ?? 0;

                        return "
                            <div class='small'>
                                <div class='fw-bold'>Total: {$total} observations</div>
                                <div><i class='ri-time-line'></i> {$time} ({$duration} min)</div>
                            </div>
                        ";
                    } catch (\Exception $e) {
                        Log::error('Error in observation_summary column: ' . $e->getMessage());
                        return 'Error loading summary';
                    }
                })
                ->addColumn('observations_breakdown', function ($observation) {
                    try {
                        $breakdown = [
                            ['type' => 'At Risk', 'count' => $observation->at_risk_behavior ?? 0, 'color' => 'danger'],
                            ['type' => 'Near Miss', 'count' => $observation->nearmiss_incident ?? 0, 'color' => 'warning'],
                            ['type' => 'Risk Mgmt', 'count' => $observation->informal_risk_mgmt ?? 0, 'color' => 'info'],
                            ['type' => 'SIM K3', 'count' => $observation->sim_k3 ?? 0, 'color' => 'primary']
                        ];

                        $html = '<div class="small">';
                        foreach ($breakdown as $item) {
                            if ($item['count'] > 0) {
                                $html .= "<div><span class='badge bg-{$item['color']} me-1'>{$item['count']}</span>{$item['type']}</div>";
                            }
                        }
                        $html .= '</div>';

                        return $html ?: '<span class="text-muted">No observations</span>';
                    } catch (\Exception $e) {
                        Log::error('Error in observations_breakdown: ' . $e->getMessage());
                        return '<span class="text-muted">Error loading breakdown</span>';
                    }
                })
                ->addColumn('status_badge', function ($observation) {
                    $colors = [
                        'draft' => 'secondary',
                        'submitted' => 'warning',
                        'reviewed' => 'success'
                    ];
                    $labels = [
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed'
                    ];
                    $status = $observation->status ?? 'unknown';
                    $color = $colors[$status] ?? 'secondary';
                    $label = $labels[$status] ?? $status;
                    return "<span class='badge bg-{$color}'>{$label}</span>";
                })
                ->addColumn('notes_excerpt', function ($observation) {
                    $notes = $observation->notes ?? '';
                    return strlen($notes) > 100
                        ? substr($notes, 0, 100) . '...'
                        : ($notes ?: '<span class="text-muted">No notes</span>');
                })
                ->addColumn('created_at_formatted', function ($observation) {
                    try {
                        return $observation->created_at ? $observation->created_at->format('d M Y, H:i') : 'N/A';
                    } catch (\Exception $e) {
                        return 'Invalid date';
                    }
                })
                ->addColumn('action', function ($observation) {
                    $buttons = "
                        <div class='btn-group btn-group-sm' role='group'>
                            <button type='button' class='btn btn-outline-info' onclick='viewObservation({$observation->id})' title='View Details'>
                                <i class='ri-eye-line'></i>
                            </button>";

                    if ($observation->status === 'draft') {
                        $buttons .= "
                            <button type='button' class='btn btn-outline-primary' onclick='editObservation({$observation->id})' title='Edit Observation'>
                                <i class='ri-edit-line'></i>
                            </button>";
                    }

                    if ($observation->status === 'submitted') {
                        $buttons .= "
                            <button type='button' class='btn btn-outline-success' onclick='reviewObservation({$observation->id})' title='Mark as Reviewed'>
                                <i class='ri-check-line'></i>
                            </button>";
                    }

                    if ($observation->status === 'draft') {
                        $buttons .= "
                            <button type='button' class='btn btn-outline-danger' onclick='deleteObservation({$observation->id})' title='Delete Observation'>
                                <i class='ri-delete-bin-line'></i>
                            </button>";
                    }

                    $buttons .= "</div>";
                    return $buttons;
                })
                ->rawColumns(['observer_info', 'observation_summary', 'observations_breakdown', 'status_badge', 'notes_excerpt', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Observations DataTables Error: ' . $e->getMessage());
            Log::error('Observations DataTables Error Trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'draw' => $request->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $observation = Observation::with([
                'user:id,name,email,department',
                'details.category:id,name'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $observation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Observation not found: ' . $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'waktu_observasi' => 'required|date_format:H:i',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.description' => 'required|string|max:1000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $observationData = $request->only([
                'user_id',
                'waktu_observasi',
                'waktu_mulai',
                'waktu_selesai',
                'notes'
            ]);

            $observation = Observation::create($observationData);

            // Create observation details and count each type
            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            foreach ($request->details as $detail) {
                ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $detail['observation_type'],
                    'category_id' => $detail['category_id'],
                    'description' => $detail['description'],
                    'severity' => $detail['severity'],
                    'action_taken' => $detail['action_taken'] ?? null,
                ]);

                $counters[$detail['observation_type']]++;
            }

            // Update counters
            $observation->update($counters);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observation created successfully',
                'data' => $observation
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create observation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'waktu_observasi' => 'required|date_format:H:i',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array|min:1',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.description' => 'required|string|max:1000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $observation = Observation::findOrFail($id);

            if (!$observation->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft observations can be edited'
                ], 400);
            }

            $observationData = $request->only([
                'user_id',
                'waktu_observasi',
                'waktu_mulai',
                'waktu_selesai',
                'notes'
            ]);

            $observation->update($observationData);

            // Delete existing details and recreate
            $observation->details()->delete();

            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            foreach ($request->details as $detail) {
                ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $detail['observation_type'],
                    'category_id' => $detail['category_id'],
                    'description' => $detail['description'],
                    'severity' => $detail['severity'],
                    'action_taken' => $detail['action_taken'] ?? null,
                ]);

                $counters[$detail['observation_type']]++;
            }

            $observation->update($counters);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observation updated successfully',
                'data' => $observation
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update observation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $observation = Observation::findOrFail($id);

            if (!$observation->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft observations can be deleted'
                ], 400);
            }

            $observation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Observation deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete observation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:submitted,reviewed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $observation = Observation::findOrFail($id);

            if ($request->status === 'submitted' && !$observation->canBeSubmitted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Observation must be in draft status and have at least one detail'
                ], 400);
            }

            if ($request->status === 'reviewed' && $observation->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted observations can be reviewed'
                ], 400);
            }

            $observation->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Observation status updated successfully',
                'data' => $observation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics()
    {
        try {
            $stats = [
                'total_observations' => Observation::count(),
                'draft_observations' => Observation::where('status', 'draft')->count(),
                'submitted_observations' => Observation::where('status', 'submitted')->count(),
                'reviewed_observations' => Observation::where('status', 'reviewed')->count(),
                'type_stats' => [
                    'at_risk_behavior' => Observation::sum('at_risk_behavior'),
                    'nearmiss_incident' => Observation::sum('nearmiss_incident'),
                    'informal_risk_mgmt' => Observation::sum('informal_risk_mgmt'),
                    'sim_k3' => Observation::sum('sim_k3'),
                ],
                'monthly_observations' => Observation::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'completion_rate' => [
                    'reviewed' => Observation::where('status', 'reviewed')->count(),
                    'total' => Observation::count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            $users = User::whereIn('role', ['employee', 'hse_staff'])->where('is_active', true)->get();
            $categories = Category::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'categories' => $categories
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getAnalyticsData()
    {
        try {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            return [
                'summary' => [
                    'total_observations' => Observation::count(),
                    'this_month' => Observation::whereMonth('created_at', now()->month)->count(),
                    'last_month' => Observation::whereBetween('created_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->count(),
                    'pending_review' => Observation::where('status', 'submitted')->count(),
                    'high_severity_count' => ObservationDetail::whereIn('severity', ['high', 'critical'])->count(),
                ],
                'trends' => $this->getMonthlyTrends(),
                'type_analysis' => $this->getTypeAnalysis(),
                'severity_analysis' => $this->getSeverityAnalysis(),
                'observer_performance' => $this->getObserverPerformance(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get analytics data: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyTrends()
    {
        return Observation::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "reviewed" THEN 1 ELSE 0 END) as reviewed,
                SUM(at_risk_behavior + nearmiss_incident + informal_risk_mgmt + sim_k3) as total_details
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                $item->review_rate = $item->total > 0 ? round(($item->reviewed / $item->total) * 100, 1) : 0;
                return $item;
            });
    }

    private function getTypeAnalysis()
    {
        return DB::table('observation_details')
            ->selectRaw('
                observation_type,
                COUNT(*) as count,
                AVG(CASE WHEN severity = "critical" THEN 4 WHEN severity = "high" THEN 3 WHEN severity = "medium" THEN 2 ELSE 1 END) as avg_severity_score
            ')
            ->groupBy('observation_type')
            ->get();
    }

    private function getSeverityAnalysis()
    {
        return DB::table('observation_details')
            ->selectRaw('
                severity,
                COUNT(*) as count,
                observation_type
            ')
            ->groupBy('severity', 'observation_type')
            ->get()
            ->groupBy('severity');
    }

    private function getObserverPerformance()
    {
        return User::whereIn('role', ['employee', 'hse_staff'])
            ->where('is_active', true)
            ->withCount([
                'observations',
                'observations as reviewed_observations_count' => function ($query) {
                    $query->where('status', 'reviewed');
                },
                'observations as this_month_observations_count' => function ($query) {
                    $query->whereMonth('created_at', now()->month);
                }
            ])
            ->having('observations_count', '>', 0)
            ->get()
            ->map(function ($user) {
                $user->review_rate = $user->observations_count > 0
                    ? round(($user->reviewed_observations_count / $user->observations_count) * 100, 1)
                    : 0;
                return $user;
            });
    }
}
