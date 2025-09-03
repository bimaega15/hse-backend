<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{

    public function create()
    {
        try {
            $employees = User::where('role', 'employee')->where('is_active', true)->get();
            $hseStaff = User::where('role', 'hse_staff')->where('is_active', true)->get();
            $categories = Category::where('is_active', true)->get();
            $contributingFactors = Contributing::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'employees' => $employees,
                    'hse_staff' => $hseStaff,
                    'categories' => $categories,
                    'contributing_factors' => $contributingFactors
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'contributing_id' => 'required|exists:contributings,id',
            'action_id' => 'required|exists:actions,id',
            'severity_rating' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|max:2000',
            'location' => 'required|string|max:255',
            'action_taken' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            $reportData = $request->only([
                'employee_id',
                'category_id',
                'contributing_id',
                'action_id',
                'severity_rating',
                'description',
                'location',
                'action_taken'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reports', 'public');
                    $imagePaths[] = $path;
                }
                $reportData['images'] = $imagePaths;
            }

            $report = Report::create($reportData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $report = Report::with([
                'employee:id,name,email',
                'hseStaff:id,name,email',
                'categoryMaster:id,name,description',
                'contributingMaster:id,name,description',
                'actionMaster:id,name,description',
                'reportDetails.approvedBy:id,name',
                'reportDetails.createdBy:id,name'
            ])->findOrFail($id);

            // Get actions for the selected contributing factor
            $actions = [];
            if ($report->contributing_id) {
                $actions = Action::where('contributing_id', $report->contributing_id)
                    ->where('is_active', true)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $report,
                'actions' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found: ' . $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'contributing_id' => 'required|exists:contributings,id',
            'action_id' => 'required|exists:actions,id',
            'severity_rating' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|max:2000',
            'location' => 'required|string|max:255',
            'action_taken' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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

            $report = Report::findOrFail($id);

            $reportData = $request->only([
                'employee_id',
                'category_id',
                'contributing_id',
                'action_id',
                'severity_rating',
                'description',
                'location',
                'action_taken'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                // Delete old images
                if ($report->images) {
                    foreach ($report->images as $oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reports', 'public');
                    $imagePaths[] = $path;
                }
                $reportData['images'] = $imagePaths;
            }

            $report->update($reportData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $report = Report::findOrFail($id);

            // Delete associated images
            if ($report->images) {
                foreach ($report->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            // Soft delete report (this will also soft delete report details due to cascade)
            $report->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:waiting,in-progress,done',
            'hse_staff_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = Report::findOrFail($id);

            $updateData = ['status' => $request->status];

            // Set timestamps based on status
            if ($request->status === 'in-progress' && !$report->start_process_at) {
                $updateData['start_process_at'] = now();
            }

            if ($request->status === 'done') {
                $updateData['completed_at'] = now();
            }

            // Assign HSE staff if provided
            if ($request->hse_staff_id) {
                $updateData['hse_staff_id'] = $request->hse_staff_id;
            }

            $report->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Report status updated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatistics()
    {
        try {
            $stats = [
                'total_reports' => Report::count(),
                'waiting_reports' => Report::where('status', 'waiting')->count(),
                'in_progress_reports' => Report::where('status', 'in-progress')->count(),
                'completed_reports' => Report::where('status', 'done')->count(),
                'severity_stats' => [
                    'low' => Report::where('severity_rating', 'low')->count(),
                    'medium' => Report::where('severity_rating', 'medium')->count(),
                    'high' => Report::where('severity_rating', 'high')->count(),
                    'critical' => Report::where('severity_rating', 'critical')->count(),
                ],
                'monthly_reports' => Report::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
                'completion_rate' => [
                    'completed' => Report::where('status', 'done')->count(),
                    'total' => Report::count()
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

    public function getActionsByContributing($contributingId)
    {
        try {
            $actions = Action::where('contributing_id', $contributingId)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get actions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // Determine view type - default, pending, or analytics
        $view = $request->get('view', 'default');
        $status = $request->get('status');

        // Pass additional data for analytics view
        $additionalData = [];

        if ($view === 'analytics') {
            $additionalData = $this->getAnalyticsData();
        }

        return view('admin.reports.index', compact('view', 'status', 'additionalData'));
    }

    public function getData(Request $request)
    {
        try {
            Log::info('DataTables Request Parameters:', $request->all());

            $query = Report::with([
                'employee:id,name,email',
                'hseStaff:id,name,email',
                'categoryMaster:id,name',
                'contributingMaster:id,name',
                'actionMaster:id,name'
            ])->orderBy('created_at', 'desc');

            // Apply filters with validation
            if ($request->filled('status') && in_array($request->status, ['waiting', 'in-progress', 'done'])) {
                $query->where('status', $request->status);
            }

            if ($request->filled('severity') && in_array($request->severity, ['low', 'medium', 'high', 'critical'])) {
                $query->where('severity_rating', $request->severity);
            }

            if ($request->filled('start_date') && strtotime($request->start_date)) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date') && strtotime($request->end_date)) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // NEW: Handle URL filters (like from sidebar)
            if ($request->filled('url_status') && in_array($request->url_status, ['waiting', 'in-progress', 'done'])) {
                $query->where('status', $request->url_status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_info', function ($report) {
                    try {
                        $employeeName = optional($report->employee)->name ?? 'N/A';
                        $userId = $report->employee_id ?? 'N/A';
                        return "<div class='fw-bold'>{$employeeName}</div><small class='text-muted'>User ID: {$userId}</small>";
                    } catch (\Exception $e) {
                        Log::error('Error in employee_info column: ' . $e->getMessage());
                        return 'Error loading employee';
                    }
                })
                ->addColumn('hse_staff_info', function ($report) {
                    try {
                        if ($report->hseStaff) {
                            $hseName = $report->hseStaff->name;
                            $hseUserId = $report->hse_staff_id;
                            return "<div class='fw-bold'>{$hseName}</div><small class='text-muted'>User ID: {$hseUserId}</small>";
                        }
                        return '<span class="text-muted">Not Assigned</span>';
                    } catch (\Exception $e) {
                        Log::error('Error in hse_staff_info column: ' . $e->getMessage());
                        return 'Error loading HSE staff';
                    }
                })
                ->addColumn('report_info', function ($report) {
                    try {
                        $category = optional($report->categoryMaster)->name ?? 'N/A';
                        $location = $report->location ?: 'N/A';
                        return "<div class='fw-bold'>{$category}</div><small class='text-muted'><i class='ri-map-pin-line'></i> {$location}</small>";
                    } catch (\Exception $e) {
                        Log::error('Error in report_info column: ' . $e->getMessage());
                        return 'Error loading report info';
                    }
                })
                ->addColumn('severity_badge', function ($report) {
                    $colors = [
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'dark'
                    ];
                    $severity = $report->severity_rating ?? 'unknown';
                    $color = $colors[$severity] ?? 'secondary';
                    return "<span class='badge bg-{$color}'>" . ucfirst($severity) . "</span>";
                })
                ->addColumn('status_badge', function ($report) {
                    $colors = [
                        'waiting' => 'warning',
                        'in-progress' => 'info',
                        'done' => 'success'
                    ];
                    $labels = [
                        'waiting' => 'Waiting',
                        'in-progress' => 'In Progress',
                        'done' => 'Completed'
                    ];
                    $status = $report->status ?? 'unknown';
                    $color = $colors[$status] ?? 'secondary';
                    $label = $labels[$status] ?? $status;
                    return "<span class='badge bg-{$color}'>{$label}</span>";
                })
                ->addColumn('report_details_count', function ($report) {
                    try {
                        $totalDetails = $report->reportDetails()->count();
                        if ($totalDetails === 0) {
                            return '<span class="text-muted">No CAR</span>';
                        }

                        $closedDetails = $report->reportDetails()->where('status_car', 'closed')->count();
                        $openDetails = $report->reportDetails()->where('status_car', 'open')->count();

                        $completionPercentage = $totalDetails > 0 ? round(($closedDetails / $totalDetails) * 100, 2) : 0;
                        $progressClass = $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger');

                        return "
                            <div class='small'>
                                <div class='fw-bold'>CAR: {$totalDetails}</div>
                                <div class='text-success'>Closed: {$closedDetails}</div>
                                <div class='text-danger'>Open: {$openDetails}</div>
                                <div class='progress mt-1' style='height: 4px;'>
                                    <div class='progress-bar bg-{$progressClass}' style='width: {$completionPercentage}%'></div>
                                </div>
                                <small class='text-muted'>{$completionPercentage}% Complete</small>
                            </div>
                        ";
                    } catch (\Exception $e) {
                        Log::error('Error in report_details_count: ' . $e->getMessage());
                        return '<span class="text-muted">Error loading CAR</span>';
                    }
                })
                ->addColumn('dates_info', function ($report) {
                    try {
                        $created = $report->created_at ? $report->created_at->locale('id')->isoFormat('DD MMMM YYYY') : 'N/A';

                        return "
                            <div class='small'>
                                <div> {$created}</div>
                            </div>
                        ";
                    } catch (\Exception $e) {
                        Log::error('Error in dates_info: ' . $e->getMessage());
                        return 'Error loading dates';
                    }
                })
                ->addColumn('action', function ($report) {
                    $buttons = "
                        <div class='btn-group btn-group-sm' role='group'>
                            <button type='button' class='btn btn-outline-info' onclick='viewReport({$report->id})' title='View Details'>
                                <i class='ri-eye-line'></i>
                            </button>
                            <button type='button' class='btn btn-outline-primary' onclick='editReport({$report->id})' title='Edit Report'>
                                <i class='ri-edit-line'></i>
                            </button>";

                    if ($report->status !== 'done') {
                        $nextStatus = $report->status === 'waiting' ? 'in-progress' : 'done';
                        $buttons .= "
                            <button type='button' class='btn btn-outline-success' onclick='updateStatus({$report->id}, \"{$nextStatus}\")' title='Update Status'>
                                <i class='ri-check-line'></i>
                            </button>";
                    }

                    $buttons .= "
                            <button type='button' class='btn btn-outline-danger' onclick='deleteReport({$report->id})' title='Delete Report'>
                                <i class='ri-delete-bin-line'></i>
                            </button>
                        </div>
                    ";

                    return $buttons;
                })
                ->rawColumns(['employee_info', 'hse_staff_info', 'report_info', 'severity_badge', 'status_badge', 'report_details_count', 'dates_info', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('DataTables Error: ' . $e->getMessage());
            Log::error('DataTables Error File: ' . $e->getFile());
            Log::error('DataTables Error Line: ' . $e->getLine());
            Log::error('DataTables Error Trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'draw' => $request->get('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    // NEW: Get analytics data
    private function getAnalyticsData()
    {
        try {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            return [
                'summary' => [
                    'total_reports' => Report::count(),
                    'this_month' => Report::whereMonth('created_at', now()->month)->count(),
                    'last_month' => Report::whereBetween('created_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->count(),
                    'critical_incidents' => Report::whereIn('severity_rating', ['high', 'critical'])->count(),
                    'overdue_cars' => DB::table('report_details')
                        ->where('due_date', '<', now())
                        ->where('status_car', '!=', 'closed')
                        ->count(),
                ],
                'trends' => $this->getMonthlyTrends(),
                'categories' => $this->getCategoryBreakdown(),
                'severity_analysis' => $this->getSeverityAnalysis(),
                'completion_metrics' => $this->getCompletionMetrics(),
                'hse_performance' => $this->getHSEPerformance(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get analytics data: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyTrends()
    {
        return Report::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN severity_rating IN ("high", "critical") THEN 1 ELSE 0 END) as critical
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                $item->completion_rate = $item->total > 0 ? round(($item->completed / $item->total) * 100, 1) : 0;
                return $item;
            });
    }

    private function getCategoryBreakdown()
    {
        return Report::join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as category,
                COUNT(*) as total,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as completed,
                AVG(CASE 
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at) 
                    ELSE NULL 
                END) as avg_resolution_hours
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    private function getSeverityAnalysis()
    {
        return Report::selectRaw('
                severity_rating,
                COUNT(*) as count,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed,
                AVG(CASE 
                    WHEN start_process_at IS NOT NULL AND completed_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, start_process_at, completed_at) 
                    ELSE NULL 
                END) as avg_resolution_hours
            ')
            ->groupBy('severity_rating')
            ->get();
    }

    private function getCompletionMetrics()
    {
        $totalReports = Report::count();
        $completedReports = Report::where('status', 'done')->count();
        $avgResolutionTime = Report::whereNotNull('start_process_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_process_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total_reports' => $totalReports,
            'completed_reports' => $completedReports,
            'completion_rate' => $totalReports > 0 ? round(($completedReports / $totalReports) * 100, 1) : 0,
            'avg_resolution_hours' => $avgResolutionTime ? round($avgResolutionTime, 1) : 0,
            'sla_compliance' => $this->calculateSLACompliance(),
        ];
    }

    private function getHSEPerformance()
    {
        return User::where('role', 'hse_staff')
            ->where('is_active', true)
            ->withCount([
                'assignedReports',
                'assignedReports as completed_reports_count' => function ($query) {
                    $query->where('status', 'done');
                },
                'assignedReports as this_month_reports_count' => function ($query) {
                    $query->whereMonth('created_at', now()->month);
                }
            ])
            ->get()
            ->map(function ($staff) {
                $staff->completion_rate = $staff->assigned_reports_count > 0
                    ? round(($staff->completed_reports_count / $staff->assigned_reports_count) * 100, 1)
                    : 0;
                return $staff;
            });
    }

    private function calculateSLACompliance()
    {
        // Define SLA targets (in hours) based on severity
        $slaTargets = [
            'critical' => 4,   // 4 hours
            'high' => 24,      // 24 hours
            'medium' => 72,    // 72 hours
            'low' => 168       // 168 hours (1 week)
        ];

        $compliance = [];

        foreach ($slaTargets as $severity => $targetHours) {
            $reports = Report::where('severity_rating', $severity)
                ->whereNotNull('start_process_at')
                ->whereNotNull('completed_at')
                ->get();

            $withinSLA = $reports->filter(function ($report) use ($targetHours) {
                $resolutionHours = $report->start_process_at->diffInHours($report->completed_at);
                return $resolutionHours <= $targetHours;
            })->count();

            $compliance[$severity] = [
                'total' => $reports->count(),
                'within_sla' => $withinSLA,
                'compliance_rate' => $reports->count() > 0 ? round(($withinSLA / $reports->count()) * 100, 1) : 0,
                'target_hours' => $targetHours
            ];
        }

        return $compliance;
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = Report::with([
                'employee:id,name,email',
                'hseStaff:id,name,email',
                'categoryMaster:id,name',
                'contributingMaster:id,name',
                'actionMaster:id,name'
            ]);

            // Apply same filters as DataTables
            if ($request->filled('status') && in_array($request->status, ['waiting', 'in-progress', 'done'])) {
                $query->where('status', $request->status);
            }

            if ($request->filled('severity') && in_array($request->severity, ['low', 'medium', 'high', 'critical'])) {
                $query->where('severity_rating', $request->severity);
            }

            if ($request->filled('start_date') && strtotime($request->start_date)) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date') && strtotime($request->end_date)) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $reports = $query->orderBy('created_at', 'desc')->get();

            // Create CSV content with proper Excel formatting
            $csvContent = $this->generateExcelContent($reports);

            $filename = 'reports_export_' . date('Y-m-d_H-i-s') . '.csv';

            return response($csvContent)
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateExcelContent($reports)
    {
        // Create CSV with BOM for proper Excel UTF-8 support
        $csvContent = "\xEF\xBB\xBF";
        
        // Headers
        $headers = [
            'No',
            'Tanggal Dibuat',
            'ID Employee',
            'Nama Employee',
            'Email Employee',
            'ID BAIK Staff',
            'Nama BAIK Staff',
            'Email BAIK Staff',
            'Kategori',
            'Contributing Factor',
            'Action',
            'Lokasi',
            'Deskripsi',
            'Severity Rating',
            'Status',
            'Action Taken',
            'Tanggal Mulai Proses',
            'Tanggal Selesai'
        ];
        
        $csvContent .= '"' . implode('","', $headers) . '"' . "\r\n";
        
        // Data rows
        $no = 1;
        foreach ($reports as $report) {
            $row = [
                $no++,
                $report->created_at ? $report->created_at->locale('id')->isoFormat('DD MMMM YYYY HH:mm') : 'N/A',
                $report->employee_id ?? 'N/A',
                optional($report->employee)->name ?? 'N/A',
                optional($report->employee)->email ?? 'N/A',
                $report->hse_staff_id ?? 'N/A',
                optional($report->hseStaff)->name ?? 'Belum Ditugaskan',
                optional($report->hseStaff)->email ?? 'N/A',
                optional($report->categoryMaster)->name ?? 'N/A',
                optional($report->contributingMaster)->name ?? 'N/A',
                optional($report->actionMaster)->name ?? 'N/A',
                $report->location ?? 'N/A',
                $this->cleanTextForCsv($report->description ?? 'N/A'),
                ucfirst($report->severity_rating ?? 'N/A'),
                $this->getStatusLabel($report->status ?? 'N/A'),
                $this->cleanTextForCsv($report->action_taken ?? 'Belum Ada Tindakan'),
                $report->start_process_at ? $report->start_process_at->locale('id')->isoFormat('DD MMMM YYYY HH:mm') : 'Belum Dimulai',
                $report->completed_at ? $report->completed_at->locale('id')->isoFormat('DD MMMM YYYY HH:mm') : 'Belum Selesai'
            ];
            
            // Escape and format each field
            $escapedRow = array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row);
            
            $csvContent .= implode(',', $escapedRow) . "\r\n";
        }
        
        return $csvContent;
    }

    private function cleanTextForCsv($text)
    {
        // Remove HTML tags and clean up text for CSV
        $text = strip_tags($text);
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'waiting' => 'Menunggu',
            'in-progress' => 'Sedang Diproses',
            'done' => 'Selesai'
        ];
        return $labels[$status] ?? $status;
    }
}
