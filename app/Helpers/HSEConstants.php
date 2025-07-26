<?php
// app/Helpers/HSEConstants.php (Updated)

namespace App\Helpers;

class HSEConstants
{
    // Severity ratings for reports
    const SEVERITY_RATINGS = ['low', 'medium', 'high', 'critical'];

    // Report status
    const REPORT_STATUS = [
        'waiting' => 'Menunggu',
        'in-progress' => 'Diproses',
        'done' => 'Selesai',
    ];

    // Notification types and categories
    const NOTIFICATION_TYPES = ['info', 'warning', 'error', 'success'];
    const NOTIFICATION_CATEGORIES = ['reports', 'reminders', 'system', 'urgent'];

    // Severity rating labels
    const SEVERITY_LABELS = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'critical' => 'Kritis'
    ];

    // Severity rating colors
    const SEVERITY_COLORS = [
        'low' => '#4CAF50',      // Green
        'medium' => '#FF9800',   // Orange
        'high' => '#F44336',     // Red
        'critical' => '#212121'  // Dark
    ];

    public static function getStatusColor($status)
    {
        $colors = [
            'waiting' => '#FF9800',
            'in-progress' => '#2196F3',
            'done' => '#4CAF50',
        ];

        return $colors[$status] ?? '#9E9E9E';
    }

    public static function getStatusText($status)
    {
        return self::REPORT_STATUS[$status] ?? $status;
    }

    public static function getSeverityColor($severity)
    {
        return self::SEVERITY_COLORS[$severity] ?? '#9E9E9E';
    }

    public static function getSeverityText($severity)
    {
        return self::SEVERITY_LABELS[$severity] ?? ucfirst($severity);
    }

    public static function isValidSeverity($severity)
    {
        return in_array($severity, self::SEVERITY_RATINGS);
    }

    public static function isValidNotificationType($type)
    {
        return in_array($type, self::NOTIFICATION_TYPES);
    }

    public static function isValidNotificationCategory($category)
    {
        return in_array($category, self::NOTIFICATION_CATEGORIES);
    }

    public static function getSeverityPriority($severity)
    {
        $priorities = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4
        ];

        return $priorities[$severity] ?? 0;
    }

    public static function getHighPrioritySeverities()
    {
        return ['high', 'critical'];
    }

    public static function getLowPrioritySeverities()
    {
        return ['low', 'medium'];
    }

    // Get all severity options for dropdowns
    public static function getSeverityOptions()
    {
        $options = [];
        foreach (self::SEVERITY_RATINGS as $severity) {
            $options[] = [
                'value' => $severity,
                'label' => self::getSeverityText($severity),
                'color' => self::getSeverityColor($severity),
                'priority' => self::getSeverityPriority($severity)
            ];
        }
        return $options;
    }

    // Check if severity is high priority
    public static function isHighPriority($severity)
    {
        return in_array($severity, self::getHighPrioritySeverities());
    }

    // Check if severity is low priority  
    public static function isLowPriority($severity)
    {
        return in_array($severity, self::getLowPrioritySeverities());
    }
}
