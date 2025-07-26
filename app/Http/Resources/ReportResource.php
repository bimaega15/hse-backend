<?php
// app/Http/Resources/ReportResource.php (Updated - Removed ObservationForm)

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'department' => $this->employee->department,
            ],
            'hse_staff' => $this->when($this->hseStaff, [
                'id' => $this->hseStaff?->id,
                'name' => $this->hseStaff?->name,
                'department' => $this->hseStaff?->department,
            ]),

            // Master data
            'category' => $this->when($this->categoryMaster, [
                'id' => $this->categoryMaster?->id,
                'name' => $this->categoryMaster?->name,
                'description' => $this->categoryMaster?->description,
            ]),
            'contributing' => $this->when($this->contributingMaster, [
                'id' => $this->contributingMaster?->id,
                'name' => $this->contributingMaster?->name,
                'description' => $this->contributingMaster?->description,
            ]),
            'action' => $this->when($this->actionMaster, [
                'id' => $this->actionMaster?->id,
                'name' => $this->actionMaster?->name,
                'description' => $this->actionMaster?->description,
            ]),

            // Report details
            'severity_rating' => $this->severity_rating,
            'severity_label' => $this->severity_label,
            'severity_color' => $this->severity_color,
            'action_taken' => $this->action_taken,
            'has_action_taken' => $this->has_action_taken,
            'description' => $this->description,
            'location' => $this->location,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),

            // Hierarchy info
            'contributing_action_hierarchy' => $this->contributing_action_hierarchy,
            'report_summary' => $this->report_summary,

            // Images
            'images' => $this->images,
            'image_urls' => $this->image_urls,

            // Status flags
            'is_completed' => $this->is_completed,
            'is_in_progress' => $this->is_in_progress,
            'is_waiting' => $this->is_waiting,

            // Timestamps
            'start_process_at' => $this->start_process_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Processing time
            'processing_time_hours' => $this->processing_time_hours,
        ];
    }
}
