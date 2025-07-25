<?php
// app/Helpers/HSEConstants.php

namespace App\Helpers;

class HSEConstants
{
    const CATEGORIES = ['Life Safety Equipment', 'Emergency Equipment', 'Electrical Equipment', 'Mechanical Equipment', 'Others'];

    const EQUIPMENT_TYPES = ['Fire Extinguisher', 'Emergency Light', 'Smoke Detector', 'Fire Alarm', 'Others'];

    const CONTRIBUTING_FACTORS = ['Defective machinery/equipment', 'Life Safety Equipment', 'Improper procedure', 'Lack of maintenance', 'Others'];

    const REPORT_STATUS = [
        'waiting' => 'Menunggu',
        'in-progress' => 'Diproses',
        'done' => 'Selesai',
    ];

    const NOTIFICATION_TYPES = ['info', 'warning', 'error', 'success'];

    const NOTIFICATION_CATEGORIES = ['reports', 'reminders', 'system', 'urgent'];

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

    public static function isValidCategory($category)
    {
        return in_array($category, self::CATEGORIES);
    }

    public static function isValidEquipmentType($equipmentType)
    {
        return in_array($equipmentType, self::EQUIPMENT_TYPES);
    }

    public static function isValidContributingFactor($factor)
    {
        return in_array($factor, self::CONTRIBUTING_FACTORS);
    }

    public static function isValidNotificationType($type)
    {
        return in_array($type, self::NOTIFICATION_TYPES);
    }

    public static function isValidNotificationCategory($category)
    {
        return in_array($category, self::NOTIFICATION_CATEGORIES);
    }
}
