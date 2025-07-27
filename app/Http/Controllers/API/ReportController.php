<?php
// app/Http/Controllers/API/ReportController.php (Updated - Removed ObservationForm)

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreReportRequest;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of reports with filtering, search, and pagination
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Report::with([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        } elseif ($user->role === 'hse_staff') {
            // HSE staff can see all reports or assigned reports
            if ($request->filter === 'assigned') {
                $query->where('hse_staff_id', $user->id);
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity !== 'all') {
            $query->where('severity_rating', $request->severity);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by contributing
        if ($request->has('contributing_id') && $request->contributing_id !== 'all') {
            $query->where('contributing_id', $request->contributing_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('action_taken', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categoryMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contributingMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('actionMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Store a newly created report
     */
    public function store(StoreReportRequest $request)
    {
        try {
            $user = $request->user();

            // Check if user is employee
            if ($user->role !== 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya karyawan yang dapat membuat laporan'
                ], 403);
            }

            // Prepare report data
            $reportData = [
                'employee_id' => $user->id,
                'category_id' => $request->category_id,
                'contributing_id' => $request->contributing_id,
                'action_id' => $request->action_id,
                'severity_rating' => $request->severity_rating,
                'action_taken' => $request->action_taken,
                'description' => $request->description,
                'location' => $request->location,
                'status' => 'waiting'
            ];

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imagePaths = $this->uploadReportImages($request->file('images'));
                $reportData['images'] = $imagePaths;
            }

            // Create the report
            $report = Report::create($reportData);
            $report->load([
                'employee',
                'categoryMaster',
                'contributingMaster',
                'actionMaster'
            ]);

            Log::info('Report created successfully', [
                'report_id' => $report->id,
                'employee_id' => $user->id,
                'category_id' => $request->category_id,
                'severity_rating' => $request->severity_rating
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $report
            ], 201);
        } catch (\Exception $e) {
            Log::error('Report creation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified report
     */
    public function show(Request $request, $id)
    {
        $report = Report::with([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        $user = $request->user();

        // Authorization check
        if ($user->role === 'employee' && $report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke laporan ini',
                'error_code' => 'FORBIDDEN'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update the specified report
     */
    public function update(Request $request, $id)
    {
        try {
            $report = Report::with(['employee'])->find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            $user = $request->user();

            // Authorization check: Only employee who created the report can update
            if ($user->role !== 'employee' || $report->employee_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat memperbarui laporan yang Anda buat sendiri.'
                ], 403);
            }

            // Status check: Only reports with 'waiting' status can be updated
            if ($report->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat diperbarui. Status sudah berubah dari \'waiting\'.'
                ], 400);
            }

            // Validate input data
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:categories,id',
                'contributing_id' => 'required|exists:contributings,id',
                'action_id' => 'required|exists:actions,id',
                'severity_rating' => 'required|in:low,medium,high,critical',
                'action_taken' => 'nullable|string|max:1000',
                'description' => 'required|string|max:1000',
                'location' => 'required|string|max:255',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Prepare update data
            $updateData = $request->only([
                'category_id',
                'contributing_id',
                'action_id',
                'severity_rating',
                'action_taken',
                'description',
                'location'
            ]);

            // Handle image uploads if new images are provided
            if ($request->hasFile('images')) {
                // Delete old images
                $this->deleteReportImages($report);

                // Upload new images
                $imagePaths = $this->uploadReportImages($request->file('images'));
                $updateData['images'] = $imagePaths;

                Log::info('Report images updated', [
                    'report_id' => $report->id,
                    'old_images_count' => count($report->images ?? []),
                    'new_images_count' => count($imagePaths)
                ]);
            }

            // Update the report
            $report->update($updateData);
            $report->load([
                'employee',
                'hseStaff',
                'categoryMaster',
                'contributingMaster',
                'actionMaster'
            ]);

            Log::info('Report updated successfully', [
                'report_id' => $report->id,
                'updated_by' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diperbarui',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Report update failed', [
                'report_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified report from storage
     */
    public function destroy($id)
    {
        try {
            $report = Report::with(['employee'])->find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            $user = request()->user();

            // Authorization check: Only employee who created the report can delete
            if ($user->role !== 'employee' || $report->employee_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menghapus laporan yang Anda buat sendiri.'
                ], 403);
            }

            // Status check: Only reports with 'waiting' status can be deleted
            if ($report->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat dihapus. Status sudah berubah dari \'waiting\'.'
                ], 400);
            }

            // Delete associated images from storage
            $this->deleteReportImages($report);

            // Delete the report from database
            $report->delete();

            Log::info('Report deleted successfully', [
                'report_id' => $report->id,
                'deleted_by' => $user->id,
                'category_id' => $report->category_id,
                'location' => $report->location
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Report deletion failed', [
                'report_id' => $id,
                'user_id' => request()->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HSE Staff starts processing a report
     */
    public function startProcess(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action_taken' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        if ($report->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan harus dalam status waiting',
            ], 400);
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat memproses laporan',
            ], 403);
        }

        // Update report status and assign HSE staff
        $updateData = [
            'status' => 'in-progress',
            'start_process_at' => now(),
            'hse_staff_id' => $request->user()->id,
        ];

        // Add action taken if provided
        if ($request->filled('action_taken')) {
            $updateData['action_taken'] = $request->action_taken;
        }

        $report->update($updateData);
        $report->load([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        Log::info('Report processing started', [
            'report_id' => $report->id,
            'hse_staff_id' => $request->user()->id,
            'severity_rating' => $report->severity_rating
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan mulai diproses',
            'data' => $report,
        ]);
    }

    /**
     * HSE Staff completes a report
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action_taken' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        if ($report->status !== 'in-progress') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan harus dalam status in-progress',
            ], 400);
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat menyelesaikan laporan',
            ], 403);
        }

        // Update report status and action taken
        $report->update([
            'status' => 'done',
            'completed_at' => now(),
            'action_taken' => $request->action_taken,
        ]);

        $report->load([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        Log::info('Report completed', [
            'report_id' => $report->id,
            'hse_staff_id' => $request->user()->id,
            'completion_time_hours' => $report->processing_time_hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diselesaikan',
            'data' => $report,
        ]);
    }

    /**
     * Get reports statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Report::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $totalReports = $query->count();
        $waitingReports = (clone $query)->where('status', 'waiting')->count();
        $inProgressReports = (clone $query)->where('status', 'in-progress')->count();
        $completedReports = (clone $query)->where('status', 'done')->count();

        // Severity statistics
        $severityStats = (clone $query)
            ->selectRaw('severity_rating, COUNT(*) as count')
            ->groupBy('severity_rating')
            ->pluck('count', 'severity_rating')
            ->toArray();

        // Category statistics with master data
        $categoryStats = (clone $query)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(*) as count')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('count', 'category_name')
            ->toArray();

        // Monthly reports data
        $monthlyData = $this->getMonthlyReportsData($user);

        return response()->json([
            'success' => true,
            'data' => [
                'total_reports' => $totalReports,
                'waiting_reports' => $waitingReports,
                'in_progress_reports' => $inProgressReports,
                'completed_reports' => $completedReports,
                'completion_rate' => $totalReports > 0 ? round(($completedReports / $totalReports) * 100, 1) : 0,
                'severity_statistics' => $severityStats,
                'category_statistics' => $categoryStats,
                'monthly_data' => $monthlyData,
                'average_completion_time' => $this->getAverageCompletionTime($user),
            ],
        ]);
    }

    /**
     * Upload report images
     */
    private function uploadReportImages($images)
    {
        $imagePaths = [];

        if ($images) {
            foreach ($images as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('report_images', $imageName, 'public');
                $imagePaths[] = $imagePath;
            }
        }

        return $imagePaths;
    }

    /**
     * Delete report images
     */
    private function deleteReportImages(Report $report)
    {
        if ($report->images) {
            foreach ($report->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }
    }

    /**
     * Get monthly reports data
     */
    private function getMonthlyReportsData(User $user, int $months = 6)
    {
        $query = Report::query();

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        return $query
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                return $item;
            });
    }


    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Report::query();

            // Filter by user role
            if ($user->role === 'employee') {
                $query->where('employee_id', $user->id);
            }

            // Get status counts
            $statusCounts = [
                'pending' => (clone $query)->where('status', 'waiting')->count(),
                'progress' => (clone $query)->where('status', 'in-progress')->count(),
                'completed' => (clone $query)->where('status', 'done')->count(),
            ];

            // Calculate total and completion rate
            $totalReports = array_sum($statusCounts);
            $completionRate = $totalReports > 0
                ? round(($statusCounts['completed'] / $totalReports) * 100, 1)
                : 0;

            // Get recent 5 reports with relationships
            $recentReports = (clone $query)
                ->with([
                    'employee:id,name,email',
                    'hseStaff:id,name,email',
                    'categoryMaster:id,name',
                    'contributingMaster:id,name',
                    'actionMaster:id,name'
                ])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'title' => $report->title,
                        'description' => $report->description,
                        'status' => $report->status,
                        'status_label' => $this->getStatusLabel($report->status),
                        'status_color' => $this->getStatusColor($report->status),
                        'severity_rating' => $report->severity_rating,
                        'severity_label' => $report->severity_label,
                        'severity_color' => $report->severity_color,
                        'location' => $report->location,
                        'category' => $report->categoryMaster?->name,
                        'employee' => [
                            'id' => $report->employee?->id,
                            'name' => $report->employee?->name,
                        ],
                        'hse_staff' => [
                            'id' => $report->hseStaff?->id,
                            'name' => $report->hseStaff?->name,
                        ],
                        'created_at' => $report->created_at,
                        'created_at_human' => $report->created_at->diffForHumans(),
                        'completed_at' => $report->completed_at,
                        'processing_time_hours' => $report->processing_time_hours,
                    ];
                });

            // Additional dashboard metrics
            $additionalMetrics = [
                'this_week' => (clone $query)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => (clone $query)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'average_completion_time' => $this->getAverageCompletionTime($user),
            ];

            // Get active banners for homepage
            $activeBanners = Banner::active()->ordered()->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'sort_order' => $banner->sort_order,
                ];
            });

            $dashboardData = [
                'status_counts' => $statusCounts,
                'total_reports' => $totalReports,
                'completion_rate' => $completionRate,
                'recent_reports' => $recentReports,
                'metrics' => $additionalMetrics,
                'banners' => $activeBanners,
            ];

            return $this->successResponse(
                $dashboardData,
                'Dashboard data retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve dashboard data: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'waiting' => 'Menunggu',
            'in-progress' => 'Dalam Proses',
            'done' => 'Selesai',
            default => ucfirst($status)
        };
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'waiting' => 'warning',
            'in-progress' => 'info',
            'done' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Calculate average completion time
     */
    private function getAverageCompletionTime($user): ?float
    {
        $query = Report::query()
            ->whereNotNull('start_process_at')
            ->whereNotNull('completed_at');

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return null;
        }

        $totalHours = $reports->sum(function ($report) {
            return $report->start_process_at->diffInHours($report->completed_at);
        });

        return round($totalHours / $reports->count(), 1);
    }
}
