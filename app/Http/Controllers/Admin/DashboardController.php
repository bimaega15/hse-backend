<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Report;
use App\Models\Observation;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\ReportDetail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Get dashboard data for initial page load
        $dashboardData = $this->getDashboardData();

        return view('admin.dashboard.index', compact('dashboardData'));
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getData(): JsonResponse
    {
        try {
            $data = $this->getDashboardData();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent reports for dashboard
     */
    public function getRecentReports(): JsonResponse
    {
        try {
            $recentReports = Report::with([
                'employee:id,name,department',
                'categoryMaster:id,name',
                'hseStaff:id,name'
            ])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'reporter' => $report->employee->name,
                        'department' => $report->employee->department ?? 'N/A',
                        'category' => $report->categoryMaster->name ?? 'N/A',
                        'severity' => ucfirst($report->severity_rating),
                        'status' => $this->getStatusLabel($report->status),
                        'date' => $report->created_at->diffForHumans(),
                        'location' => $report->location,
                        'hse_staff' => $report->hseStaff->name ?? 'Unassigned',
                        'avatarClass' => $this->getAvatarClass($report->severity_rating)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentReports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load recent reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive dashboard data
     */
    private function getDashboardData(): array
    {
        // Basic statistics
        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'waiting')->count();
        $inProgressReports = Report::where('status', 'in-progress')->count();
        $completedReports = Report::where('status', 'done')->count();

        // Critical incidents (high and critical severity)
        $criticalIncidents = Report::whereIn('severity_rating', ['high', 'critical'])
            ->whereMonth('created_at', now()->month)
            ->count();

        // Completion rate
        $completionRate = $totalReports > 0 ?
            round(($completedReports / $totalReports) * 100, 1) : 0;

        // Severity distribution
        $severityStats = Report::select('severity_rating', DB::raw('count(*) as count'))
            ->groupBy('severity_rating')
            ->pluck('count', 'severity_rating')
            ->toArray();

        // Monthly trend data (last 12 months)
        $monthlyTrend = $this->getMonthlyTrendData();

        // Category statistics
        $categoryStats = Report::join('categories', 'reports.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'name')
            ->toArray();

        // Average resolution time
        $avgResolutionTime = $this->getAverageResolutionTime();

        // User statistics
        $totalUsers = User::count();
        $hseStaff = User::where('role', 'hse_staff')->where('is_active', true)->count();
        $employees = User::where('role', 'employee')->where('is_active', true)->count();

        // Recent observations
        $totalObservations = Observation::count();
        $submittedObservations = Observation::where('status', 'submitted')->count();
        $reviewedObservations = Observation::where('status', 'reviewed')->count();

        // Report details statistics
        $totalReportDetails = ReportDetail::count();
        $openReportDetails = ReportDetail::where('status_car', 'open')->count();
        $overdueReportDetails = ReportDetail::where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->count();

        // System alerts
        $systemAlerts = $this->getSystemAlerts();

        // Recent activity
        $recentActivity = $this->getRecentActivity();

        return [
            'statistics' => [
                'total_reports' => $totalReports,
                'pending_reports' => $pendingReports,
                'in_progress_reports' => $inProgressReports,
                'completed_reports' => $completedReports,
                'critical_incidents' => $criticalIncidents,
                'completion_rate' => $completionRate,
                'avg_resolution_time' => $avgResolutionTime,
                'total_users' => $totalUsers,
                'hse_staff' => $hseStaff,
                'employees' => $employees,
                'total_observations' => $totalObservations,
                'submitted_observations' => $submittedObservations,
                'reviewed_observations' => $reviewedObservations,
                'total_report_details' => $totalReportDetails,
                'open_report_details' => $openReportDetails,
                'overdue_report_details' => $overdueReportDetails,
            ],
            'charts' => [
                'severity_distribution' => $severityStats,
                'monthly_trend' => $monthlyTrend,
                'category_statistics' => $categoryStats,
            ],
            'alerts' => $systemAlerts,
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * Get monthly trend data for charts
     */
    private function getMonthlyTrendData(): array
    {
        $months = [];
        $completed = [];
        $inProgress = [];
        $waiting = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');

            $monthCompleted = Report::where('status', 'done')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $monthInProgress = Report::where('status', 'in-progress')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $monthWaiting = Report::where('status', 'waiting')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $months[] = $monthName;
            $completed[] = $monthCompleted;
            $inProgress[] = $monthInProgress;
            $waiting[] = $monthWaiting;
        }

        return [
            'months' => $months,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'waiting' => $waiting,
        ];
    }

    /**
     * Calculate average resolution time in days
     */
    private function getAverageResolutionTime(): string
    {
        $completedReports = Report::whereNotNull('start_process_at')
            ->whereNotNull('completed_at')
            ->where('status', 'done')
            ->get();

        if ($completedReports->isEmpty()) {
            return '0 days';
        }

        $totalHours = $completedReports->sum(function ($report) {
            return $report->start_process_at->diffInHours($report->completed_at);
        });

        $avgHours = $totalHours / $completedReports->count();
        $avgDays = round($avgHours / 24, 1);

        return $avgDays . ' days';
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Critical incidents alert
        $criticalCount = Report::whereIn('severity_rating', ['critical'])
            ->whereIn('status', ['waiting', 'in-progress'])
            ->count();

        if ($criticalCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Critical Incident Alert',
                'message' => "{$criticalCount} critical incidents require immediate attention. Review and assign to HSE staff.",
                'action' => 'Review Now',
                'icon' => 'ri-error-warning-line'
            ];
        }

        // Overdue reports alert
        $overdueCount = ReportDetail::where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->count();

        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Reports',
                'message' => "{$overdueCount} reports are overdue for resolution. Follow up with assigned HSE staff.",
                'action' => 'View Details',
                'icon' => 'ri-time-line'
            ];
        }

        // Pending reviews alert
        $pendingReviews = Observation::where('status', 'submitted')->count();

        if ($pendingReviews > 5) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Reviews',
                'message' => "{$pendingReviews} observations are pending review by HSE staff.",
                'action' => 'Review',
                'icon' => 'ri-file-list-line'
            ];
        }

        return $alerts;
    }

    /**
     * Get recent activity timeline
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        // Recent completed reports
        $recentCompleted = Report::where('status', 'done')
            ->with(['employee', 'hseStaff'])
            ->latest('completed_at')
            ->limit(5)
            ->get();

        foreach ($recentCompleted as $report) {
            $activities[] = [
                'type' => 'success',
                'icon' => 'ri-check-line',
                'title' => 'Report Completed',
                'description' => $report->hseStaff->name . ' completed investigation for report #' . $report->id,
                'time' => $report->completed_at->diffForHumans(),
                'timestamp' => $report->completed_at->timestamp
            ];
        }

        // Recent critical reports
        $recentCritical = Report::whereIn('severity_rating', ['critical', 'high'])
            ->with(['employee'])
            ->latest()
            ->limit(3)
            ->get();

        foreach ($recentCritical as $report) {
            $activities[] = [
                'type' => 'warning',
                'icon' => 'ri-alert-line',
                'title' => 'New ' . ucfirst($report->severity_rating) . ' Incident',
                'description' => $report->description,
                'time' => $report->created_at->diffForHumans(),
                'timestamp' => $report->created_at->timestamp
            ];
        }

        // Sort by timestamp descending
        usort($activities, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'waiting' => 'Waiting',
            'in-progress' => 'In Progress',
            'done' => 'Completed',
            default => ucfirst($status)
        };
    }

    /**
     * Get avatar class based on severity
     */
    private function getAvatarClass(string $severity): string
    {
        return match ($severity) {
            'critical' => 'bg-danger-subtle',
            'high' => 'bg-warning-subtle',
            'medium' => 'bg-info-subtle',
            'low' => 'bg-success-subtle',
            default => 'bg-secondary-subtle'
        };
    }

    /**
     * Get dashboard statistics for specific date range
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $statistics = [
                'reports' => [
                    'total' => Report::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'completed' => Report::where('status', 'done')
                        ->whereBetween('created_at', [$startDate, $endDate])->count(),
                    'pending' => Report::where('status', 'waiting')
                        ->whereBetween('created_at', [$startDate, $endDate])->count(),
                    'critical' => Report::whereIn('severity_rating', ['critical', 'high'])
                        ->whereBetween('created_at', [$startDate, $endDate])->count(),
                ],
                'observations' => [
                    'total' => Observation::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'submitted' => Observation::where('status', 'submitted')
                        ->whereBetween('created_at', [$startDate, $endDate])->count(),
                    'reviewed' => Observation::where('status', 'reviewed')
                        ->whereBetween('created_at', [$startDate, $endDate])->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
