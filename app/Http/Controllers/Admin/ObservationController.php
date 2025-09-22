<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\User;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use App\Models\Project;
use App\Models\Activator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
                'details.category:id,name',
                'details.location:id,name',
                'details.project:id,project_name'
            ])->orderBy('observations.created_at', 'desc');

            Log::info('Initial query built successfully');

            // Keep URL status filter for backward compatibility with existing links
            if ($request->filled('status') && in_array($request->status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->status);
            }

            // NEW: Handle URL filters
            if ($request->filled('url_status') && in_array($request->url_status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->url_status);
            }

            // ENHANCED: Multiple filter options

            // Filter by observer/user (only remaining filter that we use)
            if ($request->filled('observer_id') && $request->observer_id !== '') {
                Log::info('Applying observer filter: ' . $request->observer_id);
                $query->where('user_id', $request->observer_id);
            }

            // Filter by date range
            if ($request->filled('date_from') && $request->date_from !== '') {
                Log::info('Applying date_from filter: ' . $request->date_from);
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to') && $request->date_to !== '') {
                Log::info('Applying date_to filter: ' . $request->date_to);
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Filter by location
            if ($request->filled('location_id') && $request->location_id !== '') {
                Log::info('Applying location filter: ' . $request->location_id);
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('location_id', $request->location_id);
                });
            }

            // Filter by project
            if ($request->filled('project_id') && $request->project_id !== '') {
                Log::info('Applying project filter: ' . $request->project_id);
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('project_id', $request->project_id);
                });
            }

            // Filter by category
            if ($request->filled('category_id') && $request->category_id !== '') {
                Log::info('Applying category filter: ' . $request->category_id);
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('category_id', $request->category_id);
                });
            }

            // Filter by action
            if ($request->filled('action_id') && $request->action_id !== '') {
                Log::info('Applying action filter: ' . $request->action_id);
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('action_id', $request->action_id);
                });
            }

            // Filter by contributing factor
            if ($request->filled('contributing_id') && $request->contributing_id !== '') {
                Log::info('Applying contributing filter: ' . $request->contributing_id);
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('contributing_id', $request->contributing_id);
                });
            }

            // Handle search filter (removed but keeping the structure for potential future use)
            // Search functionality has been removed from UI but keeping controller logic

            Log::info('All filters applied successfully');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('observer_info', function ($observation) {
                    try {
                        if (!$observation) {
                            return 'No data';
                        }

                        $observerName = 'N/A';
                        $department = 'N/A';
                        $userId = $observation->user_id ?? 'N/A';

                        if ($observation->user) {
                            $observerName = $observation->user->name ?? 'N/A';
                            $department = $observation->user->department ?? 'N/A';
                        }

                        return "<div class='fw-bold'>" . htmlspecialchars($observerName) . "</div><small class='text-muted'>Dept: " . htmlspecialchars($department) . " | ID: {$userId}</small>";
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
            Log::error('Request params: ' . json_encode($request->all()));
            Log::error('Observations DataTables Error Trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }

    // NEW METHOD: Get Recent Observations for Dashboard
    public function getRecent(Request $request)
    {
        try {
            $limit = $request->get('limit', 5); // Default 5 recent observations

            $observations = Observation::with([
                'user:id,name,email,department',
                'details:id,observation_id,observation_type,severity'
            ])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $formattedObservations = $observations->map(function ($observation) {
                // Get the primary observation type (most frequent)
                $detailCounts = $observation->details->groupBy('observation_type');
                $primaryType = $detailCounts->sortByDesc(function ($details) {
                    return $details->count();
                })->keys()->first();

                // Format type name
                $typeMap = [
                    'at_risk_behavior' => 'At Risk',
                    'nearmiss_incident' => 'Near Miss',
                    'informal_risk_mgmt' => 'Risk Mgmt',
                    'sim_k3' => 'SIM K3'
                ];

                // Get avatar class based on observation type
                $avatarClassMap = [
                    'at_risk_behavior' => 'bg-danger-subtle',
                    'nearmiss_incident' => 'bg-warning-subtle',
                    'informal_risk_mgmt' => 'bg-info-subtle',
                    'sim_k3' => 'bg-primary-subtle'
                ];

                return [
                    'id' => $observation->id,
                    'observer' => optional($observation->user)->name ?? 'N/A',
                    'department' => optional($observation->user)->department ?? 'N/A',
                    'type' => $typeMap[$primaryType] ?? 'Unknown',
                    'count' => $observation->total_observations,
                    'status' => ucfirst($observation->status),
                    'date' => $observation->created_at->diffForHumans(),
                    'avatarClass' => $avatarClassMap[$primaryType] ?? 'bg-secondary-subtle'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedObservations
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching recent observations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load recent observations: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $observation = Observation::with([
                'user:id,name,email,department',
                'details.category:id,name',
                'details.contributing:id,name',
                'details.action:id,name',
                'details.location:id,name',
                'details.project:id,project_name',
                'details.activator:id,name'
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
        try {
            // Handle JSON request
            $jsonData = $request->isJson() ? $request->all() : json_decode($request->getContent(), true);

            if (!$jsonData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data format'
                ], 400);
            }

            $validator = Validator::make($jsonData, [
                'user_id' => 'required|exists:users,id',
                'waktu_observasi' => 'required|date_format:H:i',
                'waktu_mulai' => 'required|date_format:H:i',
                'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
                'notes' => 'nullable|string|max:1000',
                'details' => 'required|array|min:1',
                'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
                'details.*.category_id' => 'required|exists:categories,id',
                'details.*.contributing_id' => 'required|exists:contributings,id',
                'details.*.action_id' => 'required|exists:actions,id',
                'details.*.location_id' => 'required|exists:locations,id',
                'details.*.project_id' => 'nullable|exists:projects,id',
                'details.*.activator_id' => 'nullable|exists:activators,id',
                'details.*.report_date' => 'required|date',
                'details.*.description' => 'required|string|max:2000',
                'details.*.severity' => 'required|in:low,medium,high,critical',
                'details.*.action_taken' => 'nullable|string|max:1000',
                'details.*.images' => 'nullable|array',
                'details.*.images.*.name' => 'required_with:details.*.images.*|string',
                'details.*.images.*.type' => 'required_with:details.*.images.*|string',
                'details.*.images.*.size' => 'required_with:details.*.images.*|integer|max:2097152', // 2MB
                'details.*.images.*.data' => 'required_with:details.*.images.*|string',
            ]);

            // Custom validation for activator_id when observation_type is at_risk_behavior
            $validator->after(function ($validator) use ($jsonData) {
                if (isset($jsonData['details'])) {
                    foreach ($jsonData['details'] as $index => $detail) {
                        if (isset($detail['observation_type']) && $detail['observation_type'] === 'at_risk_behavior') {
                            if (!isset($detail['activator_id']) || empty($detail['activator_id'])) {
                                $validator->errors()->add("details.{$index}.activator_id", 'Activator is required for At Risk Behavior observations.');
                            }
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $observation = Observation::create([
                'user_id' => $jsonData['user_id'],
                'waktu_observasi' => $jsonData['waktu_observasi'],
                'waktu_mulai' => $jsonData['waktu_mulai'],
                'waktu_selesai' => $jsonData['waktu_selesai'],
                'notes' => $jsonData['notes'] ?? null,
                'status' => 'draft'
            ]);

            // Create observation details and count each type
            $counters = [
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 0,
                'sim_k3' => 0,
            ];

            foreach ($jsonData['details'] as $detailData) {
                // Create observation detail
                $detail = ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $detailData['observation_type'],
                    'category_id' => $detailData['category_id'],
                    'contributing_id' => $detailData['contributing_id'],
                    'action_id' => $detailData['action_id'],
                    'location_id' => $detailData['location_id'],
                    'project_id' => $detailData['project_id'] ?? null,
                    'activator_id' => $detailData['activator_id'] ?? null,
                    'report_date' => $detailData['report_date'],
                    'description' => $detailData['description'],
                    'severity' => $detailData['severity'],
                    'action_taken' => $detailData['action_taken'] ?? null,
                ]);

                // Process and save images as base64
                if (isset($detailData['images']) && is_array($detailData['images'])) {
                    $imageArray = [];
                    foreach ($detailData['images'] as $imageData) {
                        $imageArray[] = [
                            'name' => $imageData['name'],
                            'type' => $imageData['type'],
                            'size' => $imageData['size'],
                            'data' => $imageData['data']
                        ];
                    }
                    // Store images as JSON in a separate field or create related model
                    $detail->update(['images' => json_encode($imageArray)]);
                }

                $counters[$detailData['observation_type']]++;
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
            'details.*.contributing_id' => 'nullable|exists:contributings,id',
            'details.*.action_id' => 'nullable|exists:actions,id',
            'details.*.location_id' => 'nullable|exists:locations,id',
            'details.*.project_id' => 'nullable|exists:projects,id',
            'details.*.activator_id' => 'nullable|exists:activators,id',
            'details.*.report_date' => 'nullable|date',
            'details.*.description' => 'required|string|max:2000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:1000',
            'details.*.images' => 'nullable|array',
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
                // Process images if present
                $images = null;
                if (isset($detail['images']) && is_array($detail['images'])) {
                    $images = json_encode($detail['images']);
                }

                ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $detail['observation_type'],
                    'category_id' => $detail['category_id'],
                    'contributing_id' => $detail['contributing_id'] ?? null,
                    'action_id' => $detail['action_id'] ?? null,
                    'location_id' => $detail['location_id'] ?? null,
                    'project_id' => $detail['project_id'] ?? null,
                    'activator_id' => $detail['activator_id'] ?? null,
                    'report_date' => $detail['report_date'] ?? null,
                    'description' => $detail['description'],
                    'severity' => $detail['severity'],
                    'action_taken' => $detail['action_taken'] ?? null,
                    'images' => $images,
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
            $contributings = Contributing::where('is_active', true)->get();
            $actions = Action::where('is_active', true)->get();
            $locations = Location::where('is_active', true)->get();
            $projects = Project::where('status', 'open')->get();
            $activators = Activator::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'categories' => $categories,
                    'contributings' => $contributings,
                    'actions' => $actions,
                    'locations' => $locations,
                    'projects' => $projects,
                    'activators' => $activators
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFilterData()
    {
        try {
            // Get all users who have observations (only HSE Staff and Employee roles)
            $observers = User::whereHas('observations')
                ->whereIn('role', ['hse_staff', 'employee'])
                ->select('id', 'name', 'department', 'role')
                ->get();

            // Get unique departments from users who have observations (only HSE Staff and Employee roles)
            $departments = User::whereHas('observations')
                ->whereIn('role', ['hse_staff', 'employee'])
                ->select('department')
                ->distinct()
                ->whereNotNull('department')
                ->pluck('department');

            // Get categories used in observation details
            $categories = Category::whereHas('observationDetails')
                ->select('id', 'name')
                ->get();

            // Get actions used in observation details
            $actions = Action::whereHas('observationDetails')
                ->select('id', 'name')
                ->get();

            // Get contributing factors used in observation details
            $contributings = Contributing::whereHas('observationDetails')
                ->select('id', 'name')
                ->get();

            // Get locations used in observation details
            $locations = Location::whereHas('observationDetails')
                ->select('id', 'name')
                ->get();

            // Get projects used in observation details
            $projects = Project::whereHas('observationDetails')
                ->select('id', 'project_name')
                ->get();

            // Static observation types
            $observationTypes = [
                ['value' => 'at_risk_behavior', 'label' => 'At Risk Behavior'],
                ['value' => 'nearmiss_incident', 'label' => 'Near Miss Incident'],
                ['value' => 'informal_risk_mgmt', 'label' => 'Informal Risk Management'],
                ['value' => 'sim_k3', 'label' => 'SIM K3']
            ];

            // Static severity levels
            $severityLevels = [
                ['value' => 'low', 'label' => 'Low'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'critical', 'label' => 'Critical']
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'observers' => $observers,
                    'departments' => $departments,
                    'categories' => $categories,
                    'actions' => $actions,
                    'contributings' => $contributings,
                    'locations' => $locations,
                    'projects' => $projects,
                    'observation_types' => $observationTypes,
                    'severity_levels' => $severityLevels
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load filter data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getIndexBehaviorData(Request $request)
    {
        try {
            // Check if all 3 required filters are provided
            if (!$request->filled('observer_id') || !$request->filled('project_id') || !$request->filled('location_id') ||
                $request->observer_id === '' || $request->project_id === '' || $request->location_id === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Observer, Project, and Location filters are required'
                ]);
            }

            $query = Observation::with(['details'])
                ->where('user_id', $request->observer_id)
                ->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('project_id', $request->project_id)
                               ->where('location_id', $request->location_id);
                });

            // Apply additional filters if provided
            if ($request->filled('date_from') && $request->date_from !== '') {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to') && $request->date_to !== '') {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('category_id') && $request->category_id !== '') {
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('category_id', $request->category_id);
                });
            }
            if ($request->filled('contributing_id') && $request->contributing_id !== '') {
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('contributing_id', $request->contributing_id);
                });
            }
            if ($request->filled('action_id') && $request->action_id !== '') {
                $query->whereHas('details', function ($detailQuery) use ($request) {
                    $detailQuery->where('action_id', $request->action_id);
                });
            }

            $observations = $query->get();

            // Calculate index behavior metrics
            $totalAtRisk = 0;
            $totalNearMiss = 0;
            $totalRiskMgmt = 0;
            $totalSimK3 = 0;
            $totalHours = 0;

            foreach ($observations as $observation) {
                $totalAtRisk += $observation->at_risk_behavior ?? 0;
                $totalNearMiss += $observation->nearmiss_incident ?? 0;
                $totalRiskMgmt += $observation->informal_risk_mgmt ?? 0;
                $totalSimK3 += $observation->sim_k3 ?? 0;
                $totalHours += $observation->duration_in_minutes / 60; // Convert minutes to hours
            }

            // Calculate index behavior
            $totalObservations = $totalAtRisk + $totalNearMiss + $totalRiskMgmt + $totalSimK3;
            $atRiskPerHari = $totalHours > 0 ? round($totalAtRisk / $totalHours, 2) : 0;
            $atRiskPerTahun = round($atRiskPerHari * 350, 2);
            $indexBehaviorValue = $this->getIndexBehaviorCategory($atRiskPerTahun);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_observations' => $totalObservations,
                    'total_at_risk' => $totalAtRisk,
                    'total_near_miss' => $totalNearMiss,
                    'total_risk_mgmt' => $totalRiskMgmt,
                    'total_sim_k3' => $totalSimK3,
                    'total_hours' => round($totalHours, 2),
                    'at_risk_per_hari' => $atRiskPerHari,
                    'at_risk_per_tahun' => $atRiskPerTahun,
                    'index_behavior' => $indexBehaviorValue
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getIndexBehaviorData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate index behavior: ' . $e->getMessage()
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

    public function exportExcel(Request $request)
    {
        try {
            $query = Observation::with([
                'user:id,name,email,department',
                'details.category:id,name',
                'details.contributing:id,name',
                'details.action:id,name',
                'details.location:id,name',
                'details.project:id,project_name',
                'details.activator:id,name'
            ]);

            // Keep URL status filter for backward compatibility
            if ($request->filled('status') && in_array($request->status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->status);
            }

            // Handle URL filters
            if ($request->filled('url_status') && in_array($request->url_status, ['draft', 'submitted', 'reviewed'])) {
                $query->where('status', $request->url_status);
            }

            // Handle search filter for export (searches in user name, project name, and location name)
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'LIKE', $searchTerm);
                    })
                    ->orWhereHas('details.project', function ($projectQuery) use ($searchTerm) {
                        $projectQuery->where('project_name', 'LIKE', $searchTerm);
                    })
                    ->orWhereHas('details.location', function ($locationQuery) use ($searchTerm) {
                        $locationQuery->where('name', 'LIKE', $searchTerm);
                    });
                });
            }

            // Handle selected_items filter from modal export
            if ($request->filled('selected_items') && is_array($request->selected_items)) {
                $selectedCombinations = [];

                // Parse selected_items format: user_id_project_id_location_id
                foreach ($request->selected_items as $item) {
                    $parts = explode('_', $item);
                    if (count($parts) === 3) {
                        $selectedCombinations[] = [
                            'user_id' => (int)$parts[0],
                            'project_id' => (int)$parts[1],
                            'location_id' => (int)$parts[2]
                        ];
                    }
                }

                if (!empty($selectedCombinations)) {
                    $query->where(function($q) use ($selectedCombinations) {
                        foreach ($selectedCombinations as $combination) {
                            $q->orWhere(function($subQ) use ($combination) {
                                $subQ->where('observations.user_id', $combination['user_id'])
                                     ->whereHas('details', function($detailQ) use ($combination) {
                                         $detailQ->where('project_id', $combination['project_id'])
                                                 ->where('location_id', $combination['location_id']);
                                     });
                            });
                        }
                    });
                }
            }

            $observations = $query->orderBy('created_at', 'desc')->get();

            // Parse selected items if available
            $selectedCombinations = [];
            if ($request->filled('selected_items') && is_array($request->selected_items)) {
                foreach ($request->selected_items as $item) {
                    $parts = explode('_', $item);
                    if (count($parts) === 3) {
                        $selectedCombinations[] = [
                            'user_id' => (int)$parts[0],
                            'project_id' => (int)$parts[1],
                            'location_id' => (int)$parts[2]
                        ];
                    }
                }
            }

            // Group observations by user, project, and location
            $groupedObservations = $this->groupObservationsByUserProjectLocation($observations, $selectedCombinations);

            // Generate Excel file with separate sheets per group
            $spreadsheet = $this->generateObservationExcelContentByGroup($groupedObservations);

            // Create writer and output file
            $writer = new Xlsx($spreadsheet);

            $filename = 'laporan_observasi_index_behavior_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'excel');

            $writer->save($tempFile);

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Observation Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export observation data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateObservationExcelContent($observations)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set worksheet title
        $sheet->setTitle('Observations');

        $currentRow = 1;

        // Title row
        $sheet->setCellValue('A' . $currentRow, 'TABEL OBSERVASI HSE');
        $sheet->mergeCells('A' . $currentRow . ':Q' . $currentRow);

        // Style title
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
        ]);

        $currentRow += 2; // Empty line

        // MAIN OBSERVATIONS SECTION
        $sheet->setCellValue('A' . $currentRow, 'DATA OBSERVASI UTAMA');
        $sheet->mergeCells('A' . $currentRow . ':Q' . $currentRow);

        // Style section header
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
        ]);

        $currentRow++;

        // Main observations headers (without detail observasi column)
        $mainHeaders = [
            'No',
            'Tanggal Dibuat',
            'ID Observer',
            'Nama Observer',
            'Email Observer',
            'Departemen',
            'Waktu Observasi',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi (Menit)',
            'Total Observasi',
            'At Risk Behavior',
            'Near Miss',
            'Risk Management',
            'SIM K3',
            'Status',
            'Catatan'
        ];

        // Set main headers
        $col = 'A';
        foreach ($mainHeaders as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }

        // Style main headers
        $sheet->getStyle('A' . $currentRow . ':Q' . $currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $currentRow++;

        // Main data rows
        $no = 1;
        foreach ($observations as $observation) {
            // Calculate duration
            $duration = 0;
            if ($observation->waktu_mulai && $observation->waktu_selesai) {
                $start = strtotime($observation->waktu_mulai);
                $end = strtotime($observation->waktu_selesai);
                $duration = ($end - $start) / 60; // Convert to minutes
            }

            $rowData = [
                $no++,
                $observation->created_at ? $observation->created_at->format('d/m/Y H:i') : 'N/A',
                $observation->user_id ?? 'N/A',
                optional($observation->user)->name ?? 'N/A',
                optional($observation->user)->email ?? 'N/A',
                optional($observation->user)->department ?? 'N/A',
                $observation->waktu_observasi ?? 'N/A',
                $observation->waktu_mulai ?? 'N/A',
                $observation->waktu_selesai ?? 'N/A',
                $duration > 0 ? round($duration, 0) . ' menit' : 'N/A',
                $observation->total_observations ?? 0,
                $observation->at_risk_behavior ?? 0,
                $observation->nearmiss_incident ?? 0,
                $observation->informal_risk_mgmt ?? 0,
                $observation->sim_k3 ?? 0,
                $this->getObservationStatusLabel($observation->status ?? 'N/A'),
                $this->cleanTextForExcel($observation->notes ?? 'Tidak ada catatan')
            ];

            $col = 'A';
            foreach ($rowData as $cellValue) {
                $sheet->setCellValue($col . $currentRow, $cellValue);
                $col++;
            }

            // Style main data row
            $sheet->getStyle('A' . $currentRow . ':Q' . $currentRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $currentRow++;
        }

        // Check if there are observation details
        $hasObservationDetails = false;
        $totalDetails = 0;
        foreach ($observations as $observation) {
            if ($observation->details && count($observation->details) > 0) {
                $hasObservationDetails = true;
                $totalDetails += count($observation->details);
            }
        }

        // OBSERVATION DETAILS section at fixed position (Row 3, Column S)
        if ($hasObservationDetails) {
            $detailsStartRow = 3;

            // OBSERVATION DETAILS SECTION
            $sheet->setCellValue('S' . $detailsStartRow, 'DETAIL OBSERVASI');
            $sheet->mergeCells('S' . $detailsStartRow . ':AE' . $detailsStartRow);

            // Style observation details header
            $sheet->getStyle('S' . $detailsStartRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
            ]);

            $detailsStartRow++;

            // Observation details headers
            $detailsHeaders = [
                'No',
                'ID Observasi',
                'Tipe Observasi',
                'Kategori',
                'Contributing Factor',
                'Action',
                'Location',
                'Project',
                'Activator',
                'Deskripsi',
                'Tingkat Keparahan',
                'Tanggal Laporan',
                'Tindakan yang Diambil'
            ];

            // Set observation details headers starting from column S
            $col = 'S';
            foreach ($detailsHeaders as $header) {
                $sheet->setCellValue($col . $detailsStartRow, $header);
                $col++;
            }

            // Style observation details headers
            $sheet->getStyle('S' . $detailsStartRow . ':AE' . $detailsStartRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $detailsStartRow++;

            // Add observation details data
            $detailNo = 1;
            foreach ($observations as $observation) {
                if ($observation->details && count($observation->details) > 0) {
                    foreach ($observation->details as $detail) {
                        $typeMap = [
                            'at_risk_behavior' => 'At Risk Behavior',
                            'nearmiss_incident' => 'Near Miss Incident',
                            'informal_risk_mgmt' => 'Informal Risk Management',
                            'sim_k3' => 'SIM K3'
                        ];

                        $detailRowData = [
                            $detailNo++,
                            $observation->id,
                            $typeMap[$detail->observation_type] ?? $detail->observation_type,
                            $this->cleanTextForExcel(optional($detail->category)->name, 'Kategori belum dipilih'),
                            $this->cleanTextForExcel(optional($detail->contributing)->name, 'Contributing factor belum dipilih'),
                            $this->cleanTextForExcel(optional($detail->action)->name, 'Action belum dipilih'),
                            $this->cleanTextForExcel(optional($detail->location)->name, 'Location belum dipilih'),
                            $this->cleanTextForExcel(optional($detail->project)->project_name, 'Project belum dipilih'),
                            $this->cleanTextForExcel(optional($detail->activator)->name, 'Activator belum dipilih'),
                            $this->cleanTextForExcel($detail->description),
                            strtoupper($detail->severity ?? 'N/A'),
                            $detail->report_date ? \Carbon\Carbon::parse($detail->report_date)->format('d/m/Y') : 'Tanggal belum diisi',
                            $this->cleanTextForExcel($detail->action_taken, 'Tidak ada tindakan')
                        ];

                        $col = 'S';
                        foreach ($detailRowData as $cellValue) {
                            $sheet->setCellValue($col . $detailsStartRow, $cellValue);
                            $col++;
                        }

                        // Style observation details data row
                        $sheet->getStyle('S' . $detailsStartRow . ':AE' . $detailsStartRow)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                        ]);

                        $detailsStartRow++;
                    }
                }
            }
        }

        // Set optimal column widths for main data
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set specific widths for observation details columns to prevent overlap
        $detailColumnWidths = [
            'S' => 8,   // No
            'T' => 12,  // ID Observasi
            'U' => 20,  // Tipe Observasi
            'V' => 25,  // Kategori
            'W' => 30,  // Contributing Factor
            'X' => 25,  // Action
            'Y' => 25,  // Location
            'Z' => 25,  // Project
            'AA' => 20, // Activator
            'AB' => 40, // Deskripsi
            'AC' => 18, // Tingkat Keparahan
            'AD' => 15, // Tanggal Laporan
            'AE' => 30  // Tindakan yang Diambil
        ];

        foreach ($detailColumnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        return $spreadsheet;
    }

    private function groupObservationsByUserProjectLocation($observations, $selectedCombinations = [])
    {
        $grouped = [];

        // If no selected combinations, use all combinations (fallback for non-modal export)
        if (empty($selectedCombinations)) {
            foreach ($observations as $observation) {
                $userName = optional($observation->user)->name ?? 'Unknown User';

                // Group by each unique project-location combination in observation details
                $combinations = [];

                foreach ($observation->details as $detail) {
                    // Get project name, fallback to 'Unknown Project' if null
                    $projectName = $detail->project ? $detail->project->project_name : 'Unknown Project';

                    // Get location name, fallback to 'Unknown Location' if null
                    $locationName = $detail->location ? $detail->location->name : 'Unknown Location';

                    $combKey = $projectName . '|' . $locationName;
                    if (!in_array($combKey, $combinations)) {
                        $combinations[] = $combKey;

                        // Create grouping key
                        $groupKey = $userName . '_' . $projectName . '_' . $locationName;

                        // Debug log
                        Log::info('Creating group key', [
                            'user_name' => $userName,
                            'project_name' => $projectName,
                            'location_name' => $locationName,
                            'group_key' => $groupKey
                        ]);

                        if (!isset($grouped[$groupKey])) {
                            $grouped[$groupKey] = [
                                'user_name' => $userName,
                                'project_name' => $projectName,
                                'location_name' => $locationName,
                                'observations' => collect([])
                            ];
                        }

                        // Clone observation for this specific combination
                        $clonedObservation = $observation->replicate();
                        $clonedObservation->setRelation('user', $observation->user);

                        // Preserve original attributes including timestamps
                        $clonedObservation->id = $observation->id;
                        $clonedObservation->created_at = $observation->created_at;
                        $clonedObservation->updated_at = $observation->updated_at;

                        // Filter details to only include this specific project-location combination
                        $filteredDetails = $observation->details->filter(function($d) use ($detail) {
                            $dProjectId = $d->project_id ?? 'null';
                            $dLocationId = $d->location_id ?? 'null';
                            $detailProjectId = $detail->project_id ?? 'null';
                            $detailLocationId = $detail->location_id ?? 'null';
                            return $dProjectId == $detailProjectId && $dLocationId == $detailLocationId;
                        });

                        $clonedObservation->setRelation('details', $filteredDetails);
                        $grouped[$groupKey]['observations']->push($clonedObservation);
                    }
                }

                // Fallback for observations without project/location details
                if (empty($combinations)) {
                    $groupKey = $userName . '_No Project_No Location';

                    if (!isset($grouped[$groupKey])) {
                        $grouped[$groupKey] = [
                            'user_name' => $userName,
                            'project_name' => 'No Project',
                            'location_name' => 'No Location',
                            'observations' => collect([])
                        ];
                    }

                    $grouped[$groupKey]['observations']->push($observation);
                }
            }
        } else {
            // Filter based on selected combinations from modal
            $users = User::pluck('name', 'id');
            $projects = Project::pluck('project_name', 'id');
            $locations = Location::pluck('name', 'id');

            foreach ($selectedCombinations as $combination) {
                $userName = $users[$combination['user_id']] ?? 'Unknown User';
                $projectName = $projects[$combination['project_id']] ?? 'Unknown Project';
                $locationName = $locations[$combination['location_id']] ?? 'Unknown Location';

                $groupKey = $userName . '_' . $projectName . '_' . $locationName;

                // Initialize group
                $grouped[$groupKey] = [
                    'user_name' => $userName,
                    'project_name' => $projectName,
                    'location_name' => $locationName,
                    'observations' => collect([])
                ];

                // Find matching observations
                foreach ($observations as $observation) {
                    if ($observation->user_id == $combination['user_id']) {
                        // Check if observation has details matching this project/location
                        $hasMatchingDetails = $observation->details->contains(function($detail) use ($combination) {
                            return $detail->project_id == $combination['project_id'] &&
                                   $detail->location_id == $combination['location_id'];
                        });

                        if ($hasMatchingDetails) {
                            // Clone observation for this specific combination
                            $clonedObservation = $observation->replicate();
                            $clonedObservation->setRelation('user', $observation->user);

                            // Preserve original attributes including timestamps
                            $clonedObservation->id = $observation->id;
                            $clonedObservation->created_at = $observation->created_at;
                            $clonedObservation->updated_at = $observation->updated_at;

                            // Filter details to only include this specific project-location combination
                            $filteredDetails = $observation->details->filter(function($d) use ($combination) {
                                return $d->project_id == $combination['project_id'] && $d->location_id == $combination['location_id'];
                            });

                            $clonedObservation->setRelation('details', $filteredDetails);
                            $grouped[$groupKey]['observations']->push($clonedObservation);
                        }
                    }
                }
            }
        }

        return $grouped;
    }

    private function generateObservationExcelContentByGroup($groupedObservations)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheetIndex = 0;

        foreach ($groupedObservations as $groupKey => $groupData) {
            // Create new worksheet or use existing one
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            // Clean sheet title with user, project, and location
            $fullSheetName = $groupData['user_name'] . '_' . $groupData['project_name'] . '_' . $groupData['location_name'];

            // Debug log
            Log::info('Creating Excel sheet', [
                'user_name' => $groupData['user_name'],
                'project_name' => $groupData['project_name'],
                'location_name' => $groupData['location_name'],
                'full_sheet_name' => $fullSheetName
            ]);

            $sheetTitle = $this->cleanSheetTitle($fullSheetName);

            // Debug log for truncation
            Log::info('Sheet title truncation', [
                'original_name' => $fullSheetName,
                'original_length' => strlen($fullSheetName),
                'truncated_name' => $sheetTitle,
                'truncated_length' => strlen($sheetTitle)
            ]);

            $sheet->setTitle($sheetTitle);

            $this->createObservationSheet($sheet, $groupData);
            $sheetIndex++;
        }

        return $spreadsheet;
    }

    private function createObservationSheet($sheet, $groupData)
    {
        $currentRow = 1;

        // Title
        $sheet->setCellValue('A' . $currentRow, 'LAPORAN OBSERVASI INDEX BEHAVIOR');
        $sheet->mergeCells('A' . $currentRow . ':K' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
        ]);
        $currentRow += 2;

        // Observer info on the left side
        $sheet->setCellValue('A' . $currentRow, 'Nama Observer');
        $sheet->setCellValue('B' . $currentRow, ': ' . $groupData['user_name']);
        // Approval info on the right side
        $sheet->setCellValue('J' . $currentRow, 'Dibuat Oleh');
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Nama Project');
        $sheet->setCellValue('B' . $currentRow, ': ' . $groupData['project_name']);
        $sheet->setCellValue('J' . $currentRow, 'Diperiksa Oleh');
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Lokasi Project');
        $sheet->setCellValue('B' . $currentRow, ': ' . $groupData['location_name']);
        $sheet->setCellValue('J' . $currentRow, 'Diketahui Oleh');
        $currentRow += 2;

        // Table headers with rowspan structure
        $headerRow1 = $currentRow;
        $headerRow2 = $currentRow + 1;

        // First row headers (with rowspan for columns A-G)
        $leftHeaders = [
            'No',
            'Lokasi Observasi',
            'Kegiatan yang di Observasi',
            'Tanggal Observasi',
            'Jam Mulai',
            'Jam Selesai',
            'Total Waktu'
        ];

        $col = 'A';
        foreach ($leftHeaders as $header) {
            $sheet->setCellValue($col . $headerRow1, $header);
            $sheet->mergeCells($col . $headerRow1 . ':' . $col . $headerRow2);
            $col++;
        }

        // "HASIL OBSERVASI" header spanning columns H-K
        $sheet->setCellValue('H' . $headerRow1, 'HASIL OBSERVASI');
        $sheet->mergeCells('H' . $headerRow1 . ':K' . $headerRow1);

        // Second row headers for result columns
        $resultHeaders = ['At Risk Behavior', 'Nearmiss', 'SIM K3', 'STAR'];
        $col = 'H';
        foreach ($resultHeaders as $header) {
            $sheet->setCellValue($col . $headerRow2, $header);
            $col++;
        }

        // Style first header row
        $sheet->getStyle('A' . $headerRow1 . ':K' . $headerRow1)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Style second header row
        $sheet->getStyle('A' . $headerRow2 . ':K' . $headerRow2)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $currentRow = $headerRow2 + 1;

        // Data rows
        $no = 1;
        $totalWaktu = 0;
        $totalAtRisk = 0;
        $totalNearmiss = 0;
        $totalSimK3 = 0;
        $totalStar = 0;

        foreach ($groupData['observations'] as $observation) {
            // Calculate duration in minutes
            $duration = 0;
            if ($observation->waktu_mulai && $observation->waktu_selesai) {
                $start = strtotime($observation->waktu_mulai);
                $end = strtotime($observation->waktu_selesai);
                $duration = ($end - $start) / 60;
            }

            // Get main location from first detail
            $mainLocation = $observation->details->first() ?
                           optional($observation->details->first()->location)->name : 'N/A';

            // Get main activity description from first detail
            $mainActivity = $observation->details->first() ?
                           $observation->details->first()->description : 'N/A';

            $rowData = [
                $no++,
                $mainLocation,
                $this->cleanTextForExcel($mainActivity),
                $observation->created_at ? $observation->created_at->format('d/m/Y') : 'N/A',
                $observation->waktu_mulai ?? 'N/A',
                $observation->waktu_selesai ?? 'N/A',
                $duration > 0 ? round($duration, 0) . ' Menit' : 'N/A',
                $observation->at_risk_behavior ?? 0,
                $observation->nearmiss_incident ?? 0,
                $observation->sim_k3 ?? 0,
                0 // STAR - not available in current data structure
            ];

            $col = 'A';
            foreach ($rowData as $cellValue) {
                $sheet->setCellValue($col . $currentRow, $cellValue);
                $col++;
            }

            // Style data row
            $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            // Add to totals
            $totalWaktu += $duration;
            $totalAtRisk += $observation->at_risk_behavior ?? 0;
            $totalNearmiss += $observation->nearmiss_incident ?? 0;
            $totalSimK3 += $observation->sim_k3 ?? 0;

            $currentRow++;
        }

        // Total row
        $totalRowData = [
            '',
            '',
            '',
            'TOTAL',
            '',
            '',
            round($totalWaktu, 0),
            $totalAtRisk,
            $totalNearmiss,
            $totalSimK3,
            $totalStar
        ];

        $col = 'A';
        foreach ($totalRowData as $cellValue) {
            $sheet->setCellValue($col . $currentRow, $cellValue);
            $col++;
        }

        // Style total row
        $sheet->getStyle('A' . $currentRow . ':K' . $currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $currentRow += 2; // Add space before index behavior calculation

        // INDEX BEHAVIOR CALCULATION SECTION
        $sheet->setCellValue('C' . $currentRow, 'Menghitung Index Behavior');
        $sheet->getStyle('C' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
        ]);
        $currentRow++;

        // Total Waktu/60 calculation
        $totalWaktuJam = round($totalWaktu / 60, 2);
        $sheet->setCellValue('C' . $currentRow, 'Total Waktu/60 =');
        $sheet->setCellValue('D' . $currentRow, $totalWaktuJam);
        $currentRow++;

        // At Risk Per Jam calculation
        $atRiskPerJam = round(0.5 * $totalAtRisk, 2);
        $sheet->setCellValue('C' . $currentRow, 'At Risk Per Jam = 0,5 * Total At Risk Behavior = 0.5 x ' . $totalAtRisk);
        $sheet->setCellValue('D' . $currentRow, $atRiskPerJam);
        $currentRow++;

        // At Risk Per Hari calculation
        $atRiskPerHari = round($atRiskPerJam * 8, 2);
        $sheet->setCellValue('C' . $currentRow, 'At Risk Per Hari = At Risk Per Jam X 8 >>> ' . $atRiskPerJam . ' X 8');
        $sheet->setCellValue('D' . $currentRow, $atRiskPerHari);
        $currentRow++;

        // At Risk Per Tahun calculation
        $atRiskPerTahun = round($atRiskPerHari * 350, 2);
        $sheet->setCellValue('C' . $currentRow, 'At Risk Per Thn = At Risk Per Hari X 350 >>> ' . $atRiskPerHari . ' X 350');
        $sheet->setCellValue('D' . $currentRow, $atRiskPerTahun);
        $currentRow++;

        // Nilai Index Behavior with condition
        $indexBehaviorValue = $this->getIndexBehaviorCategory($atRiskPerTahun);
        $sheet->setCellValue('C' . $currentRow, 'Nilai Index Behavior');
        $sheet->setCellValue('D' . $currentRow, $indexBehaviorValue['label']);

        // Apply color based on category
        $sheet->getStyle('D' . $currentRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $indexBehaviorValue['color']]],
            'font' => ['bold' => true, 'color' => ['rgb' => $indexBehaviorValue['textColor']]]
        ]);

        // Set column widths
        $columnWidths = [
            'A' => 5,   // No
            'B' => 20,  // Lokasi Observasi
            'C' => 35,  // Kegiatan yang di Observasi
            'D' => 15,  // Tanggal Observasi
            'E' => 12,  // Jam Mulai
            'F' => 12,  // Jam Selesai
            'G' => 15,  // Total Waktu
            'H' => 15,  // At Risk Behavior
            'I' => 12,  // Nearmiss
            'J' => 12,  // SIM K3
            'K' => 10   // STAR
        ];

        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
    }

    private function generateObservationExcelContentByUser($groupedObservations)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        $sheetIndex = 0;

        foreach ($groupedObservations as $userId => $userObservations) {
            // Get user info from first observation
            $firstObservation = $userObservations->first();
            $userName = optional($firstObservation->user)->name ?? 'User ID ' . $userId;

            // Create new worksheet or use existing one
            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            // Clean sheet title (Excel sheet names have restrictions)
            $sheetTitle = $this->cleanSheetTitle($userName);
            $sheet->setTitle($sheetTitle);

            $currentRow = 1;

            // Title row
            $sheet->setCellValue('A' . $currentRow, 'TABEL OBSERVASI HSE - ' . strtoupper($userName));
            $sheet->mergeCells('A' . $currentRow . ':Q' . $currentRow);

            // Style title
            $sheet->getStyle('A' . $currentRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
            ]);

            $currentRow += 2; // Empty line

            // MAIN OBSERVATIONS SECTION
            $sheet->setCellValue('A' . $currentRow, 'DATA OBSERVASI UTAMA');
            $sheet->mergeCells('A' . $currentRow . ':Q' . $currentRow);

            // Style section header
            $sheet->getStyle('A' . $currentRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
            ]);

            $currentRow++;

            // Main observations headers
            $mainHeaders = [
                'No',
                'Tanggal Dibuat',
                'ID Observer',
                'Nama Observer',
                'Email Observer',
                'Departemen',
                'Waktu Observasi',
                'Waktu Mulai',
                'Waktu Selesai',
                'Durasi (Menit)',
                'Total Observasi',
                'At Risk Behavior',
                'Near Miss',
                'Risk Management',
                'SIM K3',
                'Status',
                'Catatan'
            ];

            // Set main headers
            $col = 'A';
            foreach ($mainHeaders as $header) {
                $sheet->setCellValue($col . $currentRow, $header);
                $col++;
            }

            // Style main headers
            $sheet->getStyle('A' . $currentRow . ':Q' . $currentRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $currentRow++;

            // Main data rows for this user
            $no = 1;
            foreach ($userObservations as $observation) {
                // Calculate duration
                $duration = 0;
                if ($observation->waktu_mulai && $observation->waktu_selesai) {
                    $start = strtotime($observation->waktu_mulai);
                    $end = strtotime($observation->waktu_selesai);
                    $duration = ($end - $start) / 60; // Convert to minutes
                }

                $rowData = [
                    $no++,
                    $observation->created_at ? $observation->created_at->format('d/m/Y H:i') : 'N/A',
                    $observation->user_id ?? 'N/A',
                    optional($observation->user)->name ?? 'N/A',
                    optional($observation->user)->email ?? 'N/A',
                    optional($observation->user)->department ?? 'N/A',
                    $observation->waktu_observasi ?? 'N/A',
                    $observation->waktu_mulai ?? 'N/A',
                    $observation->waktu_selesai ?? 'N/A',
                    $duration > 0 ? round($duration, 0) . ' menit' : 'N/A',
                    $observation->total_observations ?? 0,
                    $observation->at_risk_behavior ?? 0,
                    $observation->nearmiss_incident ?? 0,
                    $observation->informal_risk_mgmt ?? 0,
                    $observation->sim_k3 ?? 0,
                    $this->getObservationStatusLabel($observation->status ?? 'N/A'),
                    $this->cleanTextForExcel($observation->notes ?? 'Tidak ada catatan')
                ];

                $col = 'A';
                foreach ($rowData as $cellValue) {
                    $sheet->setCellValue($col . $currentRow, $cellValue);
                    $col++;
                }

                // Style main data row
                $sheet->getStyle('A' . $currentRow . ':Q' . $currentRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $currentRow++;
            }

            // Check if there are observation details for this user
            $hasObservationDetails = false;
            foreach ($userObservations as $observation) {
                if ($observation->details && count($observation->details) > 0) {
                    $hasObservationDetails = true;
                    break;
                }
            }

            // OBSERVATION DETAILS section
            if ($hasObservationDetails) {
                $detailsStartRow = 3;

                // OBSERVATION DETAILS SECTION
                $sheet->setCellValue('S' . $detailsStartRow, 'DETAIL OBSERVASI');
                $sheet->mergeCells('S' . $detailsStartRow . ':AE' . $detailsStartRow);

                // Style observation details header
                $sheet->getStyle('S' . $detailsStartRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
                ]);

                $detailsStartRow++;

                // Observation details headers
                $detailsHeaders = [
                    'No',
                    'ID Observasi',
                    'Tipe Observasi',
                    'Kategori',
                    'Contributing Factor',
                    'Action',
                    'Location',
                    'Project',
                    'Activator',
                    'Deskripsi',
                    'Tingkat Keparahan',
                    'Tanggal Laporan',
                    'Tindakan yang Diambil'
                ];

                // Set observation details headers starting from column S
                $col = 'S';
                foreach ($detailsHeaders as $header) {
                    $sheet->setCellValue($col . $detailsStartRow, $header);
                    $col++;
                }

                // Style observation details headers
                $sheet->getStyle('S' . $detailsStartRow . ':AE' . $detailsStartRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);

                $detailsStartRow++;

                // Add observation details data for this user
                $detailNo = 1;
                foreach ($userObservations as $observation) {
                    if ($observation->details && count($observation->details) > 0) {
                        foreach ($observation->details as $detail) {
                            $typeMap = [
                                'at_risk_behavior' => 'At Risk Behavior',
                                'nearmiss_incident' => 'Near Miss Incident',
                                'informal_risk_mgmt' => 'Informal Risk Management',
                                'sim_k3' => 'SIM K3'
                            ];

                            $detailRowData = [
                                $detailNo++,
                                $observation->id,
                                $typeMap[$detail->observation_type] ?? $detail->observation_type,
                                $this->cleanTextForExcel(optional($detail->category)->name, 'Kategori belum dipilih'),
                                $this->cleanTextForExcel(optional($detail->contributing)->name, 'Contributing factor belum dipilih'),
                                $this->cleanTextForExcel(optional($detail->action)->name, 'Action belum dipilih'),
                                $this->cleanTextForExcel(optional($detail->location)->name, 'Location belum dipilih'),
                                $this->cleanTextForExcel(optional($detail->project)->project_name, 'Project belum dipilih'),
                                $this->cleanTextForExcel(optional($detail->activator)->name, 'Activator belum dipilih'),
                                $this->cleanTextForExcel($detail->description),
                                strtoupper($detail->severity ?? 'N/A'),
                                $detail->report_date ? \Carbon\Carbon::parse($detail->report_date)->format('d/m/Y') : 'Tanggal belum diisi',
                                $this->cleanTextForExcel($detail->action_taken, 'Tidak ada tindakan')
                            ];

                            $col = 'S';
                            foreach ($detailRowData as $cellValue) {
                                $sheet->setCellValue($col . $detailsStartRow, $cellValue);
                                $col++;
                            }

                            // Style observation details data row
                            $sheet->getStyle('S' . $detailsStartRow . ':AE' . $detailsStartRow)->applyFromArray([
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                            ]);

                            $detailsStartRow++;
                        }
                    }
                }
            }

            // Set optimal column widths for main data
            foreach (range('A', 'Q') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set specific widths for observation details columns to prevent overlap
            $detailColumnWidths = [
                'S' => 8,   // No
                'T' => 12,  // ID Observasi
                'U' => 20,  // Tipe Observasi
                'V' => 25,  // Kategori
                'W' => 30,  // Contributing Factor
                'X' => 25,  // Action
                'Y' => 25,  // Location
                'Z' => 25,  // Project
                'AA' => 20, // Activator
                'AB' => 40, // Deskripsi
                'AC' => 18, // Tingkat Keparahan
                'AD' => 15, // Tanggal Laporan
                'AE' => 30  // Tindakan yang Diambil
            ];

            foreach ($detailColumnWidths as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            $sheetIndex++;
        }

        return $spreadsheet;
    }

    private function cleanSheetTitle($title)
    {
        // Excel sheet names cannot contain these characters: \ / ? * [ ]
        $cleanTitle = preg_replace('/[\\\\\/\?\*\[\]]/', '', $title);

        // If title is within limit, return as is
        if (strlen($cleanTitle) <= 31) {
            return $cleanTitle ?: 'Sheet';
        }

        // Smart truncation for format: User_Project_Location
        $parts = explode('_', $cleanTitle);

        if (count($parts) >= 3) {
            $userName = $parts[0];
            $projectName = $parts[1];
            $locationName = $parts[2];

            // Try different truncation strategies
            $maxLength = 31;
            $separatorLength = 2; // Two underscores

            // Strategy 1: Truncate each part equally
            $availableLength = $maxLength - $separatorLength;
            $partLength = floor($availableLength / 3);

            $truncatedUser = substr($userName, 0, $partLength);
            $truncatedProject = substr($projectName, 0, $partLength);
            $truncatedLocation = substr($locationName, 0, $partLength);

            $result = $truncatedUser . '_' . $truncatedProject . '_' . $truncatedLocation;

            // If still too long, try more aggressive truncation
            if (strlen($result) > 31) {
                $partLength = 8; // Fixed 8 chars per part
                $truncatedUser = substr($userName, 0, $partLength);
                $truncatedProject = substr($projectName, 0, $partLength);
                $truncatedLocation = substr($locationName, 0, $partLength);
                $result = $truncatedUser . '_' . $truncatedProject . '_' . $truncatedLocation;
            }

            // Final fallback - just cut at 31 chars
            if (strlen($result) > 31) {
                $result = substr($result, 0, 31);
            }

            return $result;
        }

        // Fallback for other formats
        return substr($cleanTitle, 0, 31) ?: 'Sheet';
    }

    private function getObservationDetailsSummary($details)
    {
        if ($details->isEmpty()) {
            return 'Tidak ada detail observasi';
        }

        $summary = [];
        $typeMap = [
            'at_risk_behavior' => 'At Risk',
            'nearmiss_incident' => 'Near Miss',
            'informal_risk_mgmt' => 'Risk Mgmt',
            'sim_k3' => 'SIM K3'
        ];

        foreach ($details as $detail) {
            $type = $typeMap[$detail->observation_type] ?? $detail->observation_type;
            $category = optional($detail->category)->name ?? 'N/A';
            $severity = ucfirst($detail->severity ?? 'N/A');

            $summary[] = "{$type} - {$category} ({$severity}): " . substr($detail->description, 0, 100);
        }

        return implode(' | ', $summary);
    }

    private function cleanTextForCsv($text)
    {
        // Remove HTML tags and clean up text for CSV
        $text = strip_tags($text);
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function cleanTextForExcel($text, $emptyDefault = 'Tidak Diisi')
    {
        if (!$text || trim($text) === '') return $emptyDefault;

        // Convert to string if not already
        $text = (string) $text;

        // Remove HTML tags
        $text = strip_tags($text);

        // Replace line breaks with space for better display in Excel cells
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);

        // Replace multiple spaces with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove any control characters except space
        $text = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $text);

        $text = trim($text);

        return $text === '' ? $emptyDefault : $text;
    }

    private function getObservationStatusLabel($status)
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Disubmit',
            'reviewed' => 'Sudah Direview'
        ];
        return $labels[$status] ?? $status;
    }

    private function getIndexBehaviorCategory($atRiskPerTahun)
    {
        if ($atRiskPerTahun < 200) {
            return [
                'label' => 'Rendah (Safe Zone)',
                'color' => '90EE90', // Light green
                'textColor' => '000000' // Black text
            ];
        } elseif ($atRiskPerTahun >= 200 && $atRiskPerTahun <= 20000) {
            return [
                'label' => 'Sedang (Safe Zone)',
                'color' => '87CEEB', // Sky blue
                'textColor' => '000000' // Black text
            ];
        } elseif ($atRiskPerTahun > 20000 && $atRiskPerTahun <= 40000) {
            return [
                'label' => 'Tinggi (Critical Zone)',
                'color' => 'FFA500', // Orange
                'textColor' => '000000' // Black text
            ];
        } else {
            return [
                'label' => 'Sangat Tinggi (Risk Zone)',
                'color' => 'FF0000', // Red
                'textColor' => 'FFFFFF' // White text
            ];
        }
    }

    public function getGroupedExportData(Request $request)
    {
        try {
            // Start with a simpler query
            $query = ObservationDetail::select(
                'observations.user_id',
                'observation_details.project_id',
                'observation_details.location_id',
                DB::raw('COUNT(*) as count')
            )
            ->join('observations', 'observations.id', '=', 'observation_details.observation_id')
            ->whereNotNull('observation_details.location_id')
            ->whereNotNull('observation_details.project_id');

            // Keep minimal filters for export compatibility
            if ($request->filled('status')) {
                $query->where('observations.status', $request->status);
            }

            // Handle search filter for grouped export data (searches in user name, project name, and location name)
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereExists(function ($userQuery) use ($searchTerm) {
                        $userQuery->select(DB::raw(1))
                                 ->from('users')
                                 ->whereColumn('users.id', 'observations.user_id')
                                 ->where('users.name', 'LIKE', $searchTerm);
                    })
                    ->orWhereExists(function ($projectQuery) use ($searchTerm) {
                        $projectQuery->select(DB::raw(1))
                                    ->from('projects')
                                    ->whereColumn('projects.id', 'observation_details.project_id')
                                    ->where('projects.project_name', 'LIKE', $searchTerm);
                    })
                    ->orWhereExists(function ($locationQuery) use ($searchTerm) {
                        $locationQuery->select(DB::raw(1))
                                     ->from('locations')
                                     ->whereColumn('locations.id', 'observation_details.location_id')
                                     ->where('locations.name', 'LIKE', $searchTerm);
                    });
                });
            }

            // Group and get results
            $results = $query->groupBy('observations.user_id', 'observation_details.project_id', 'observation_details.location_id')
                ->get();

            // Get lookup data
            $users = User::select('id', 'name', 'role')->get()->keyBy('id');
            $projects = Project::pluck('project_name', 'id');
            $locations = Location::pluck('name', 'id');

            // Manual grouping
            $groupedData = [];

            foreach ($results as $result) {
                $userId = $result->user_id;
                $projectId = $result->project_id;
                $locationId = $result->location_id;

                // Initialize user if not exists
                if (!isset($groupedData[$userId])) {
                    $user = $users[$userId] ?? null;
                    $groupedData[$userId] = [
                        'user_id' => $userId,
                        'user_name' => $user ? $user->name : 'Unknown User',
                        'user_role' => $user ? ucfirst(str_replace('_', ' ', $user->role)) : 'Employee',
                        'projects' => []
                    ];
                }

                // Initialize project if not exists
                if (!isset($groupedData[$userId]['projects'][$projectId])) {
                    $groupedData[$userId]['projects'][$projectId] = [
                        'project_id' => $projectId,
                        'project_name' => $projects[$projectId] ?? 'Unknown Project',
                        'locations' => []
                    ];
                }

                // Add location
                $groupedData[$userId]['projects'][$projectId]['locations'][] = [
                    'location_id' => $locationId,
                    'location_name' => $locations[$locationId] ?? 'Unknown Location',
                    'count' => $result->count
                ];
            }

            // Convert to array and reindex
            $finalData = [];
            foreach ($groupedData as $userData) {
                $userData['projects'] = array_values($userData['projects']);
                $finalData[] = $userData;
            }

            return response()->json([
                'success' => true,
                'data' => $finalData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting grouped export data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load export data: ' . $e->getMessage()
            ], 500);
        }
    }
}
