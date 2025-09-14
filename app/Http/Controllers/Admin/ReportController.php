<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{

    public function create()
    {
        try {
            $employees = User::where('role', 'employee')->where('is_active', true)->get();
            $hseStaff = User::where('role', 'hse_staff')->where('is_active', true)->get();
            $categories = Category::where('is_active', true)->get();
            $contributingFactors = Contributing::where('is_active', true)->get();
            $locations = Location::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'employees' => $employees,
                    'hse_staff' => $hseStaff,
                    'categories' => $categories,
                    'contributing_factors' => $contributingFactors,
                    'locations' => $locations
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
            'location_id' => 'required|exists:locations,id',
            'project_name' => 'nullable|string|max:255',
            'action_taken' => 'nullable|string|max:1000',
            'created_at' => 'required|date',
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
                'location_id',
                'project_name',
                'action_taken',
                'created_at'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imagePaths = [];

                // Create directory if not exists
                $uploadPath = storage_path('app/public/reports');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                foreach ($request->file('images') as $index => $image) {
                    if ($image && $image->isValid()) {
                        $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

                        if ($image->move($uploadPath, $filename)) {
                            $imagePaths[] = 'reports/' . $filename;
                            Log::info("Report image uploaded successfully at index {$index}:", ['path' => 'reports/' . $filename]);
                        } else {
                            Log::error("File move failed at index {$index}");
                        }
                    } else {
                        Log::warning("Invalid or empty image file at index {$index}");
                    }
                }

                if (!empty($imagePaths)) {
                    $reportData['images'] = $imagePaths;
                } else {
                    // Only return error if there were files to upload but all failed
                    $hasFilesToUpload = false;
                    foreach ($request->file('images') as $image) {
                        if ($image) {
                            $hasFilesToUpload = true;
                            break;
                        }
                    }

                    if ($hasFilesToUpload) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mengunggah gambar'
                        ], 500);
                    }
                }
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
                'locationMaster:id,name',
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
            'location_id' => 'required|exists:locations,id',
            'project_name' => 'nullable|string|max:255',
            'action_taken' => 'nullable|string|max:1000',
            'created_at' => 'required|date',
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
                'location_id',
                'project_name',
                'action_taken',
                'created_at'
            ]);

            // Handle individual image removals
            if ($request->has('removed_images')) {
                $removedImages = json_decode($request->input('removed_images'), true);
                if (is_array($removedImages) && !empty($removedImages)) {
                    $currentImages = $report->images ?: [];

                    // Remove specified images from current images array
                    $updatedImages = array_filter($currentImages, function ($image) use ($removedImages) {
                        return !in_array($image, $removedImages);
                    });

                    // Delete the files from storage
                    foreach ($removedImages as $imagePath) {
                        if ($imagePath && is_string($imagePath)) {
                            $fullPath = storage_path('app/public/' . $imagePath);
                            if (file_exists($fullPath)) {
                                unlink($fullPath);
                                Log::info("Removed individual image:", ['path' => $imagePath]);
                            }
                        }
                    }

                    // Update the report with remaining images
                    $reportData['images'] = array_values($updatedImages);
                }
            }

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imagePaths = [];
                $hasValidImages = false;

                // Create directory if not exists
                $uploadPath = storage_path('app/public/reports');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                foreach ($request->file('images') as $index => $image) {
                    if ($image && $image->isValid()) {
                        $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;

                        if ($image->move($uploadPath, $filename)) {
                            $imagePaths[] = 'reports/' . $filename;
                            $hasValidImages = true;
                            Log::info("Report image updated successfully at index {$index}:", ['path' => 'reports/' . $filename]);
                        } else {
                            Log::error("File move failed at index {$index} during update");
                        }
                    } else {
                        Log::warning("Invalid or empty image file at index {$index} during update");
                    }
                }

                // Only delete old images if we have new valid images to replace them
                if ($hasValidImages) {
                    // Delete old images
                    if ($report->images && is_array($report->images)) {
                        foreach ($report->images as $oldImage) {
                            if ($oldImage && is_string($oldImage)) {
                                $oldImagePath = storage_path('app/public/' . $oldImage);
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                    Log::info("Deleted old report image:", ['path' => $oldImage]);
                                }
                            }
                        }
                    }
                    $reportData['images'] = $imagePaths;
                } else {
                    // Only return error if there were files to upload but all failed
                    $hasFilesToUpload = false;
                    foreach ($request->file('images') as $image) {
                        if ($image) {
                            $hasFilesToUpload = true;
                            break;
                        }
                    }

                    if ($hasFilesToUpload) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal mengunggah gambar baru'
                        ], 500);
                    }
                }
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
            if ($report->images && is_array($report->images)) {
                foreach ($report->images as $image) {
                    if ($image && is_string($image)) {
                        $imagePath = storage_path('app/public/' . $image);
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                            Log::info("Deleted report image on destroy:", ['path' => $image]);
                        }
                    }
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
            // Get filters from request
            $filters = $this->extractFilters($request);
            $additionalData = $this->getAnalyticsData($filters);
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
                'actionMaster:id,name',
                'locationMaster:id,name'
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
                        $location = optional($report->locationMaster)->name ?: 'N/A';
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
    private function getAnalyticsData($filters = [])
    {
        try {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            $completionMetrics = $this->getCompletionMetrics($filters);

            return [
                'summary' => [
                    'total_reports' => $this->getFilteredQuery($filters)->count(),
                    'this_month' => $this->getFilteredQuery(array_merge($filters, ['this_month' => true]))->count(),
                    'last_month' => $this->getFilteredQuery(array_merge($filters, ['last_month' => true]))->count(),
                    'critical_incidents' => $this->getFilteredQuery(array_merge($filters, ['high_critical' => true]))->count(),
                    'overdue_cars' => $this->getOverdueCarsCount($filters),
                    'completion_rate' => $completionMetrics['completion_rate'],
                    'avg_resolution_hours' => $completionMetrics['avg_resolution_hours'],
                    // Add detailed breakdown for Period Analysis
                    'status_breakdown' => $this->getStatusBreakdown($filters),
                    'severity_breakdown' => $this->getSeverityBreakdown($filters),
                ],
                'trends' => $this->getMonthlyTrends($filters),
                'categories' => $this->getCategoryBreakdown($filters),
                'severity_analysis' => $this->getSeverityAnalysis($filters),
                'completion_metrics' => $this->getCompletionMetrics($filters),
                'hse_performance' => $this->getHSEPerformance($filters),
                // NEW: Additional analytics reports
                'monthly_findings' => $this->getMonthlyFindingsReport($filters),
                'location_project_reports' => $this->getLocationProjectReports($filters),
                'category_detailed_reports' => $this->getCategoryDetailedReports($filters),
                'period_based_reports' => $this->getPeriodBasedReports($filters),
                // Filter options for dropdowns
                'filter_options' => $this->getFilterOptions(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get analytics data: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyTrends($filters = [])
    {
        $query = $this->getFilteredQuery($filters);

        $monthlyData = $query->selectRaw('
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

                // Get category breakdown for this month
                $categoryBreakdown = Report::join('categories', 'reports.category_id', '=', 'categories.id')
                    ->selectRaw('categories.name as category, COUNT(*) as count')
                    ->whereYear('reports.created_at', $item->year)
                    ->whereMonth('reports.created_at', $item->month)
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('count', 'desc')
                    ->get();

                $item->categories = $categoryBreakdown;
                $item->top_category = $categoryBreakdown->first()->category ?? 'N/A';
                $item->top_category_count = $categoryBreakdown->first()->count ?? 0;

                return $item;
            });

        return $monthlyData;
    }

    private function getCategoryBreakdown($filters = [])
    {
        return $this->getFilteredQuery($filters, 'reports')
            ->join('categories', 'reports.category_id', '=', 'categories.id')
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

    private function getSeverityAnalysis($filters = [])
    {
        return $this->getFilteredQuery($filters)->selectRaw('
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

    private function getCompletionMetrics($filters = [])
    {
        $totalReports = $this->getFilteredQuery($filters)->count();
        $completedReports = $this->getFilteredQuery($filters)->where('status', 'done')->count();
        $avgResolutionTime = $this->getFilteredQuery($filters)->whereNotNull('start_process_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_process_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total_reports' => $totalReports,
            'completed_reports' => $completedReports,
            'completion_rate' => $totalReports > 0 ? round(($completedReports / $totalReports) * 100, 1) : 0,
            'avg_resolution_hours' => $avgResolutionTime ? round($avgResolutionTime, 1) : 0,
            'sla_compliance' => $this->calculateSLACompliance($filters),
        ];
    }

    private function getHSEPerformance($filters = [])
    {
        // Build constraints for assigned reports based on filters
        $constraints = [];
        if (!empty($filters['start_date'])) {
            $constraints[] = ['created_at', '>=', $filters['start_date']];
        }
        if (!empty($filters['end_date'])) {
            $constraints[] = ['created_at', '<=', $filters['end_date']];
        }
        if (!empty($filters['status'])) {
            $constraints[] = ['status', '=', $filters['status']];
        }
        if (!empty($filters['severity'])) {
            $constraints[] = ['severity_rating', '=', $filters['severity']];
        }
        if (!empty($filters['category_id'])) {
            $constraints[] = ['category_id', '=', $filters['category_id']];
        }
        if (!empty($filters['location_id'])) {
            $constraints[] = ['location_id', '=', $filters['location_id']];
        }
        if (!empty($filters['project_name'])) {
            $constraints[] = ['project_name', '=', $filters['project_name']];
        }

        return User::where('role', 'hse_staff')
            ->where('is_active', true)
            ->withCount([
                'assignedReports' => function ($query) use ($constraints) {
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
                },
                'assignedReports as completed_reports_count' => function ($query) use ($constraints) {
                    $query->where('status', 'done');
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
                },
                'assignedReports as this_month_reports_count' => function ($query) use ($constraints) {
                    $query->whereMonth('created_at', now()->month);
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
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

    private function calculateSLACompliance($filters = [])
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
            $reports = $this->getFilteredQuery($filters)
                ->where('severity_rating', $severity)
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
                'actionMaster:id,name',
                'locationMaster:id,name',
                'reportDetails.createdBy:id,name',
                'reportDetails.approvedBy:id,name'
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

            // Generate Excel file using PhpSpreadsheet
            $spreadsheet = $this->generateExcelContent($reports);

            // Create writer and output file
            $writer = new Xlsx($spreadsheet);

            $filename = 'reports_export_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'excel');

            $writer->save($tempFile);

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ])->deleteFileAfterSend(true);
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
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set worksheet title
        $sheet->setTitle('Reports');

        $currentRow = 1;

        // Title row
        $sheet->setCellValue('A' . $currentRow, 'TABLE NCR & NEARMISS REPORT');
        $sheet->mergeCells('A' . $currentRow . ':R' . $currentRow);

        // Style title
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']]
        ]);

        $currentRow += 2; // Empty line

        // INITIAL REPORTING SECTION
        $sheet->setCellValue('A' . $currentRow, 'INITIAL REPORTING');
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);

        // Style section header
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
        ]);

        $currentRow++;

        // Initial reporting headers
        $initialHeaders = [
            'No', 'Date of Reporting', 'Photo', 'Explanation of Report', 'Potential Severity',
            'Category of Report', 'Location', 'Type of Report', 'Immediate Action', 'Reported By'
        ];

        // Set headers
        $col = 'A';
        foreach ($initialHeaders as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $col++;
        }

        // Style headers
        $sheet->getStyle('A' . $currentRow . ':J' . $currentRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $currentRow++;

        // Add data rows
        $no = 1;
        foreach ($reports as $report) {
            $rowData = [
                $no++,
                $report->created_at ? $report->created_at->format('d/m/Y') : 'N/A',
                count($report->images ?? []) > 0 ? 'Y' : 'N',
                $this->cleanTextForExcel($report->description),
                strtoupper($report->severity_rating ?? 'N/A'),
                $this->cleanTextForExcel(optional($report->categoryMaster)->name),
                $this->cleanTextForExcel(optional($report->locationMaster)->name),
                $this->cleanTextForExcel($this->getTypeOfReport($report)),
                $this->cleanTextForExcel($report->action_taken),
                $this->cleanTextForExcel(optional($report->employee)->name)
            ];

            $col = 'A';
            foreach ($rowData as $cellValue) {
                $sheet->setCellValue($col . $currentRow, $cellValue);
                $col++;
            }

            // Style data row
            $sheet->getStyle('A' . $currentRow . ':J' . $currentRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $currentRow++;
        }

        // Check if there are corrective actions
        $hasReportDetails = false;
        $totalCars = 0;
        foreach ($reports as $report) {
            if ($report->reportDetails && count($report->reportDetails) > 0) {
                $hasReportDetails = true;
                $totalCars += count($report->reportDetails);
            }
        }

        // CORRECTION & CORRECTIVE ACTION section at fixed position (Row 3, Column L)
        if ($hasReportDetails) {
            $correctionStartRow = 3;

            // CORRECTIVE ACTION SECTION
            $sheet->setCellValue('L' . $correctionStartRow, 'CORRECTION & CORRECTIVE ACTION');
            $sheet->mergeCells('L' . $correctionStartRow . ':R' . $correctionStartRow);

            // Style corrective action header
            $sheet->getStyle('L' . $correctionStartRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8E8E8']]
            ]);

            $correctionStartRow++;

            // Corrective action headers
            $correctionHeaders = [
                'No', 'Correction & corrective Action', 'Due Date', 'PIC',
                'Status CAR', 'Evidences', 'Approved By'
            ];

            // Set corrective action headers starting from column L
            $col = 'L';
            foreach ($correctionHeaders as $header) {
                $sheet->setCellValue($col . $correctionStartRow, $header);
                $col++;
            }

            // Style corrective action headers
            $sheet->getStyle('L' . $correctionStartRow . ':R' . $correctionStartRow)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F0F0']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $correctionStartRow++;

            // Add corrective action data
            $carNo = 1;
            foreach ($reports as $report) {
                if ($report->reportDetails && count($report->reportDetails) > 0) {
                    foreach ($report->reportDetails as $detail) {
                        $carRowData = [
                            $carNo++,
                            $this->cleanTextForExcel($detail->correction_action),
                            $detail->due_date ? \Carbon\Carbon::parse($detail->due_date)->format('d/m/Y') : 'N/A',
                            $this->cleanTextForExcel(optional($detail->createdBy)->name),
                            strtoupper($detail->status_car ?? 'OPEN'),
                            count($detail->evidences ?? []) > 0 ? 'FOTO' : 'N/A',
                            $this->cleanTextForExcel(optional($detail->approvedBy)->name)
                        ];

                        $col = 'L';
                        foreach ($carRowData as $cellValue) {
                            $sheet->setCellValue($col . $correctionStartRow, $cellValue);
                            $col++;
                        }

                        // Style corrective action data row
                        $sheet->getStyle('L' . $correctionStartRow . ':R' . $correctionStartRow)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                        ]);

                        $correctionStartRow++;
                    }
                }
            }
        }

        // Auto-size columns (including corrective action columns L-R)
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function getTypeOfReport($report)
    {
        // Determine type based on category or other criteria
        $category = optional($report->categoryMaster)->name;

        // You can customize this logic based on your categories
        if (stripos($category, 'unsafe') !== false) {
            return 'UNSAFE CONDITION';
        } elseif (stripos($category, 'near') !== false || stripos($category, 'miss') !== false) {
            return 'NEAR MISS';
        } elseif (stripos($category, 'accident') !== false || stripos($category, 'incident') !== false) {
            return 'INCIDENT';
        }

        return strtoupper($category ?? 'GENERAL');
    }

    private function cleanTextForCsv($text)
    {
        if (!$text || trim($text) === '') return 'N/A';

        // Convert to string if not already
        $text = (string) $text;

        // Remove HTML tags
        $text = strip_tags($text);

        // Replace line breaks with space
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text);

        // Replace multiple spaces with single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove problematic characters that might break CSV structure
        // Remove commas, quotes, semicolons, and tabs
        $text = str_replace(['"', "'", ',', ';', "\t"], ['', '', ' ', ' ', ' '], $text);

        // Remove any control characters except space
        $text = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '', $text);

        $text = trim($text);

        return $text === '' ? 'N/A' : $text;
    }

    private function cleanTextForHtml($text)
    {
        if (!$text || trim($text) === '') return 'N/A';

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

        return $text === '' ? 'N/A' : $text;
    }

    private function cleanTextForExcel($text)
    {
        if (!$text || trim($text) === '') return 'N/A';

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

        return $text === '' ? 'N/A' : $text;
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

    // NEW: Monthly findings report (open and closed)
    private function getMonthlyFindingsReport($filters = [])
    {
        $query = $this->getFilteredQuery($filters);

        return $query->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total_findings,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                COUNT(CASE WHEN severity_rating = "low" THEN 1 END) as low_severity,
                COUNT(CASE WHEN severity_rating = "medium" THEN 1 END) as medium_severity,
                COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_severity,
                COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_severity
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                $item->completion_rate = $item->total_findings > 0
                    ? round(($item->closed_findings / $item->total_findings) * 100, 1)
                    : 0;
                return $item;
            });
    }

    // NEW: Period-based reports (based on applied filters)
    private function getPeriodBasedReports($filters = [])
    {
        // If date filters are applied, show breakdown based on those dates
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            return $this->getFilteredPeriodBreakdown($filters);
        }

        // Otherwise show default periods
        $periods = [
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'label' => 'Today'
            ],
            'this_week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'label' => 'This Week'
            ],
            'this_month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'label' => 'This Month'
            ],
            'this_quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
                'label' => 'This Quarter'
            ],
            'this_year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'label' => 'This Year'
            ]
        ];

        $results = [];

        foreach ($periods as $key => $period) {
            // Don't override the main filters, create separate query for each period
            $basePeriodFilters = array_filter($filters, function ($value, $key) {
                return !in_array($key, ['start_date', 'end_date', 'this_month', 'last_month']);
            }, ARRAY_FILTER_USE_BOTH);

            $periodFilters = array_merge($basePeriodFilters, [
                'start_date' => $period['start']->format('Y-m-d'),
                'end_date' => $period['end']->format('Y-m-d')
            ]);

            $data = $this->getFilteredQuery($periodFilters)->selectRaw('
                    COUNT(*) as total_findings,
                    SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                    SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                    COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_findings,
                    COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_findings
                ')
                ->first();

            $results[$key] = [
                'label' => $period['label'],
                'period' => [
                    'start' => $period['start']->format('d M Y'),
                    'end' => $period['end']->format('d M Y')
                ],
                'data' => $data
            ];
        }

        return $results;
    }

    // NEW: Breakdown for filtered period
    private function getFilteredPeriodBreakdown($filters = [])
    {
        $startDate = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date']) : now()->subMonth();
        $endDate = !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date']) : now();

        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Get basic stats for the filtered period
        $data = $this->getFilteredQuery($filters)->selectRaw('
                COUNT(*) as total_findings,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_findings,
                COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_findings,
                COUNT(CASE WHEN severity_rating = "medium" THEN 1 END) as medium_findings,
                COUNT(CASE WHEN severity_rating = "low" THEN 1 END) as low_findings
            ')
            ->first();

        return [
            'filtered_period' => [
                'label' => 'Filtered Period',
                'period' => [
                    'start' => $startDate->format('d M Y'),
                    'end' => $endDate->format('d M Y'),
                    'total_days' => $totalDays
                ],
                'data' => $data,
                'avg_per_day' => $totalDays > 0 ? round(($data->total_findings ?? 0) / $totalDays, 1) : 0
            ]
        ];
    }

    // NEW: Location and project reports
    private function getLocationProjectReports($filters = [])
    {
        $locationReports = $this->getFilteredQuery($filters, 'reports')
            ->join('locations', 'reports.location_id', '=', 'locations.id')
            ->selectRaw('
                locations.name as location_name,
                COUNT(*) as total_reports,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN reports.severity_rating IN ("high", "critical") THEN 1 END) as critical_reports
            ')
            ->groupBy('locations.id', 'locations.name')
            ->orderBy('total_reports', 'desc')
            ->get();

        $projectReports = $this->getFilteredQuery($filters)
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->selectRaw('
                project_name,
                COUNT(*) as total_reports,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN severity_rating IN ("high", "critical") THEN 1 END) as critical_reports
            ')
            ->groupBy('project_name')
            ->orderBy('total_reports', 'desc')
            ->get();

        return [
            'by_location' => $locationReports,
            'by_project' => $projectReports
        ];
    }

    // NEW: Detailed category reports including unsafe conditions
    private function getCategoryDetailedReports($filters = [])
    {
        $query = $this->getFilteredQuery($filters, 'reports')
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as category_name,
                categories.description as category_description,
                COUNT(*) as total_reports,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN reports.severity_rating = "low" THEN 1 END) as low_severity,
                COUNT(CASE WHEN reports.severity_rating = "medium" THEN 1 END) as medium_severity,
                COUNT(CASE WHEN reports.severity_rating = "high" THEN 1 END) as high_severity,
                COUNT(CASE WHEN reports.severity_rating = "critical" THEN 1 END) as critical_severity,
                AVG(CASE
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('categories.id', 'categories.name', 'categories.description')
            ->orderBy('total_reports', 'desc');

        return $query->get()->map(function ($item) {
            $item->completion_rate = $item->total_reports > 0
                ? round(($item->closed_reports / $item->total_reports) * 100, 1)
                : 0;
            $item->avg_resolution_hours = $item->avg_resolution_hours
                ? round($item->avg_resolution_hours, 1)
                : 0;
            return $item;
        });
    }

    // NEW: Filter extraction and helper methods
    private function extractFilters(Request $request)
    {
        return [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'status' => $request->get('status'),
            'severity' => $request->get('severity'),
            'category_id' => $request->get('category_id'),
            'location_id' => $request->get('location_id'),
            'project_name' => $request->get('project_name'),
            'hse_staff_id' => $request->get('hse_staff_id'),
        ];
    }

    private function getFilteredQuery($filters = [], $table = 'reports')
    {
        $query = Report::query();

        // Apply date range filters UNLESS we have special month filters
        if (!empty($filters['start_date']) && empty($filters['this_month']) && empty($filters['last_month'])) {
            $query->whereDate('reports.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date']) && empty($filters['this_month']) && empty($filters['last_month'])) {
            $query->whereDate('reports.created_at', '<=', $filters['end_date']);
        }

        // Apply other filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity_rating', $filters['severity']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['project_name'])) {
            $query->where('project_name', $filters['project_name']);
        }

        if (!empty($filters['hse_staff_id'])) {
            $query->where('hse_staff_id', $filters['hse_staff_id']);
        }

        // Special filters for summary calculations (these take precedence over date range)
        if (!empty($filters['this_month'])) {
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $query->whereYear('reports.created_at', $currentYear)
                ->whereMonth('reports.created_at', $currentMonth);
        }

        if (!empty($filters['last_month'])) {
            $lastMonth = now()->subMonth();
            $query->whereYear('reports.created_at', $lastMonth->year)
                ->whereMonth('reports.created_at', $lastMonth->month);
        }

        if (!empty($filters['high_critical'])) {
            $query->whereIn('severity_rating', ['high', 'critical']);
        }

        return $query;
    }

    private function getFilterOptions()
    {
        try {
            return [
                'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'locations' => Location::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'projects' => Report::whereNotNull('project_name')
                    ->where('project_name', '!=', '')
                    ->distinct()
                    ->orderBy('project_name')
                    ->pluck('project_name'),
                'hse_staff' => User::where('role', 'hse_staff')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get filter options: ' . $e->getMessage());
            return [
                'categories' => collect(),
                'locations' => collect(),
                'projects' => collect(),
                'hse_staff' => collect()
            ];
        }
    }

    private function getOverdueCarsCount($filters = [])
    {
        $query = DB::table('report_details')
            ->join('reports', 'report_details.report_id', '=', 'reports.id')
            ->where('report_details.due_date', '<', now())
            ->where('report_details.status_car', '!=', 'closed');

        // Apply report-based filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('reports.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('reports.created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('reports.status', $filters['status']);
        }

        if (!empty($filters['severity'])) {
            $query->where('reports.severity_rating', $filters['severity']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('reports.category_id', $filters['category_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('reports.location_id', $filters['location_id']);
        }

        if (!empty($filters['project_name'])) {
            $query->where('reports.project_name', $filters['project_name']);
        }

        if (!empty($filters['hse_staff_id'])) {
            $query->where('reports.hse_staff_id', $filters['hse_staff_id']);
        }

        return $query->count();
    }

    // NEW: Get accurate status breakdown
    private function getStatusBreakdown($filters = [])
    {
        return [
            'closed' => $this->getFilteredQuery($filters)->where('status', 'done')->count(),
            'open' => $this->getFilteredQuery($filters)->whereIn('status', ['waiting', 'in-progress'])->count(),
            'waiting' => $this->getFilteredQuery($filters)->where('status', 'waiting')->count(),
            'in_progress' => $this->getFilteredQuery($filters)->where('status', 'in-progress')->count(),
        ];
    }

    // NEW: Get accurate severity breakdown
    private function getSeverityBreakdown($filters = [])
    {
        return [
            'critical' => $this->getFilteredQuery($filters)->where('severity_rating', 'critical')->count(),
            'high' => $this->getFilteredQuery($filters)->where('severity_rating', 'high')->count(),
            'medium' => $this->getFilteredQuery($filters)->where('severity_rating', 'medium')->count(),
            'low' => $this->getFilteredQuery($filters)->where('severity_rating', 'low')->count(),
        ];
    }

    // NEW: AJAX endpoint for analytics filters
    public function getAnalyticsFiltered(Request $request)
    {
        try {
            // Get filters from request
            $filters = $this->extractFilters($request);

            // Log the filters for debugging
            Log::info('Analytics filters applied:', $filters);

            // Get analytics data with filters
            $analyticsData = $this->getAnalyticsData($filters);

            // Log the summary data for debugging
            Log::info('Analytics summary data:', $analyticsData['summary'] ?? []);

            return response()->json([
                'success' => true,
                'data' => $analyticsData,
                'message' => 'Analytics data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics filter error: ' . $e->getMessage());
            Log::error('Analytics filter stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
