<?php
// app/Services/ReportService.php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Models\ObservationForm;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new report
     */
    public function createReport(array $data, User $employee, array $images = [])
    {
        // Handle image uploads
        $imagePaths = [];
        if (!empty($images)) {
            $imagePaths = $this->uploadImages($images);
        }

        $reportData = array_merge($data, [
            'employee_id' => $employee->id,
            'images' => $imagePaths,
            'status' => 'waiting',
        ]);

        $report = Report::create($reportData);
        $report->load(['employee']);

        // Send notification to HSE staff
        $this->notificationService->createNewReportNotification($report);

        return $report;
    }

    /**
     * Start processing a report
     */
    public function startProcessing(Report $report, User $hseStaff)
    {
        if ($report->status !== 'waiting') {
            throw new \Exception('Report is not in waiting status');
        }

        $previousStatus = $report->status;

        $report->update([
            'status' => 'in-progress',
            'start_process_at' => now(),
            'hse_staff_id' => $hseStaff->id,
        ]);

        $report->load(['employee', 'hseStaff']);

        // Send notification to employee
        $this->notificationService->createReportStatusUpdateNotification($report, $previousStatus);

        return $report;
    }

    /**
     * Complete a report with observation form
     */
    public function completeReport(Report $report, array $observationData)
    {
        if ($report->status !== 'in-progress') {
            throw new \Exception('Report is not in progress');
        }

        $previousStatus = $report->status;

        // Update report status
        $report->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        // Create observation form
        ObservationForm::create(
            array_merge($observationData, [
                'report_id' => $report->id,
            ]),
        );

        $report->load(['employee', 'hseStaff', 'observationForm']);

        // Send completion notification to employee
        $this->notificationService->createReportStatusUpdateNotification($report, $previousStatus);

        return $report;
    }

    /**
     * Get reports with filters
     */
    public function getReports(User $user, array $filters = [])
    {
        $query = Report::with(['employee', 'hseStaff', 'observationForm']);

        // Apply user role filter
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        } elseif ($user->role === 'hse_staff' && isset($filters['assigned']) && $filters['assigned']) {
            $query->where('hse_staff_id', $user->id);
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply date range filter
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Get reports statistics
     */
    public function getStatistics(User $user)
    {
        $query = Report::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        } elseif ($user->role === 'hse_staff') {
            // HSE staff can see all reports or only assigned reports
            // For statistics, show all reports
        }

        $total = $query->count();
        $waiting = $query->clone()->where('status', 'waiting')->count();
        $inProgress = $query->clone()->where('status', 'in-progress')->count();
        $done = $query->clone()->where('status', 'done')->count();

        // Additional statistics
        $thisMonth = $query->clone()->whereMonth('created_at', now()->month)->count();
        $thisWeek = $query
            ->clone()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $averageCompletionTime = $this->getAverageCompletionTime($user);

        return [
            'total' => $total,
            'waiting' => $waiting,
            'in_progress' => $inProgress,
            'done' => $done,
            'this_month' => $thisMonth,
            'this_week' => $thisWeek,
            'average_completion_hours' => $averageCompletionTime,
        ];
    }

    /**
     * Upload report images
     */
    private function uploadImages(array $images)
    {
        $imagePaths = [];

        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
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
    public function deleteReportImages(Report $report)
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
     * Get average completion time in hours
     */
    private function getAverageCompletionTime(User $user)
    {
        $query = Report::where('status', 'done')->whereNotNull('start_process_at')->whereNotNull('completed_at');

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $completedReports = $query->get();

        if ($completedReports->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($completedReports as $report) {
            $hours = $report->start_process_at->diffInHours($report->completed_at);
            $totalHours += $hours;
        }

        return round($totalHours / $completedReports->count(), 1);
    }

    /**
     * Get reports by category
     */
    public function getReportsByCategory(User $user)
    {
        $query = Report::query();

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        return $query->selectRaw('category, COUNT(*) as count')->groupBy('category')->orderBy('count', 'desc')->pluck('count', 'category')->toArray();
    }

    /**
     * Get monthly reports data
     */
    public function getMonthlyReportsData(User $user, int $months = 6)
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
}
