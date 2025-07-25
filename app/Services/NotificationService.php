<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\User;
use App\Models\Report;
use App\Models\Notification;

class NotificationService
{
    /**
     * Create notification for new report
     */
    public function createNewReportNotification(Report $report)
    {
        $hseStaffs = User::where('role', 'hse_staff')->where('is_active', true)->get();

        foreach ($hseStaffs as $staff) {
            $this->createNotification($staff, [
                'title' => 'Laporan Baru Diterima',
                'message' => "Ada laporan keselamatan baru dari {$report->employee->name} di {$report->location}",
                'type' => 'warning',
                'category' => 'reports',
                'data' => [
                    'report_id' => $report->id,
                    'action' => 'new_report',
                    'employee_name' => $report->employee->name,
                    'location' => $report->location,
                ],
            ]);
        }
    }

    /**
     * Create notification for report status update
     */
    public function createReportStatusUpdateNotification(Report $report, string $previousStatus)
    {
        $employee = $report->employee;

        $statusMessages = [
            'in-progress' => 'Laporan Anda sedang ditangani',
            'done' => 'Laporan Anda telah selesai ditangani',
        ];

        $message = $statusMessages[$report->status] ?? 'Status laporan Anda telah diperbarui';

        $this->createNotification($employee, [
            'title' => 'Update Status Laporan',
            'message' => "{$message} oleh " . ($report->hseStaff ? $report->hseStaff->name : 'Tim HSE'),
            'type' => $report->status === 'done' ? 'success' : 'info',
            'category' => 'reports',
            'data' => [
                'report_id' => $report->id,
                'action' => 'status_update',
                'previous_status' => $previousStatus,
                'current_status' => $report->status,
                'hse_staff_name' => $report->hseStaff ? $report->hseStaff->name : null,
            ],
        ]);
    }

    /**
     * Create reminder notification
     */
    public function createReminderNotification(User $user, string $title, string $message, array $data = [])
    {
        $this->createNotification($user, [
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'category' => 'reminders',
            'data' => array_merge($data, ['action' => 'reminder']),
        ]);
    }

    /**
     * Create system notification
     */
    public function createSystemNotification(User $user, string $title, string $message, array $data = [])
    {
        $this->createNotification($user, [
            'title' => $title,
            'message' => $message,
            'type' => 'info',
            'category' => 'system',
            'data' => array_merge($data, ['action' => 'system_update']),
        ]);
    }

    /**
     * Create urgent notification
     */
    public function createUrgentNotification(User $user, string $title, string $message, array $data = [])
    {
        $this->createNotification($user, [
            'title' => $title,
            'message' => $message,
            'type' => 'error',
            'category' => 'urgent',
            'data' => array_merge($data, ['action' => 'urgent_alert']),
        ]);
    }

    /**
     * Broadcast notification to all HSE staff
     */
    public function broadcastToHSEStaff(string $title, string $message, string $type = 'info', array $data = [])
    {
        $hseStaffs = User::where('role', 'hse_staff')->where('is_active', true)->get();

        foreach ($hseStaffs as $staff) {
            $this->createNotification($staff, [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'category' => 'system',
                'data' => array_merge($data, ['action' => 'broadcast']),
            ]);
        }
    }

    /**
     * Broadcast notification to all employees
     */
    public function broadcastToEmployees(string $title, string $message, string $type = 'info', array $data = [])
    {
        $employees = User::where('role', 'employee')->where('is_active', true)->get();

        foreach ($employees as $employee) {
            $this->createNotification($employee, [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'category' => 'system',
                'data' => array_merge($data, ['action' => 'broadcast']),
            ]);
        }
    }

    /**
     * Create a notification
     */
    private function createNotification(User $user, array $notificationData)
    {
        return $user->notifications()->create($notificationData);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsReadForUser(User $user)
    {
        return $user
            ->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications()
    {
        return Notification::where('created_at', '<', now()->subDays(30))->delete();
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCountForUser(User $user)
    {
        return $user->notifications()->whereNull('read_at')->count();
    }
}
