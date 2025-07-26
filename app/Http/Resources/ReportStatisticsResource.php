<?php
// app/Http/Resources/ReportStatisticsResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportStatisticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'summary' => [
                'total_reports' => $this->resource['total_reports'],
                'waiting_reports' => $this->resource['waiting_reports'],
                'in_progress_reports' => $this->resource['in_progress_reports'],
                'completed_reports' => $this->resource['completed_reports'],
                'completion_rate' => $this->resource['total_reports'] > 0
                    ? round(($this->resource['completed_reports'] / $this->resource['total_reports']) * 100, 1)
                    : 0,
            ],
            'severity_breakdown' => $this->formatSeverityStats($this->resource['severity_statistics']),
            'category_breakdown' => $this->resource['category_statistics'],
            'performance' => [
                'average_completion_time_hours' => $this->resource['average_completion_time'],
                'monthly_trend' => $this->resource['monthly_data'],
            ],
        ];
    }

    private function formatSeverityStats($severityStats)
    {
        $formatted = [];
        $severityLabels = [
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis'
        ];

        foreach ($severityLabels as $key => $label) {
            $formatted[] = [
                'severity' => $key,
                'label' => $label,
                'count' => $severityStats[$key] ?? 0,
                'color' => config("hse.severity_colors.{$key}", '#9E9E9E')
            ];
        }

        return $formatted;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Statistics retrieved successfully',
        ];
    }
}
