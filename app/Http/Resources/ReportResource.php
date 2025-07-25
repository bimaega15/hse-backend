<?php
// app/Http/Resources/ReportResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'equipment_type' => $this->equipment_type,
            'contributing_factor' => $this->contributing_factor,
            'description' => $this->description,
            'location' => $this->location,
            'status' => $this->status,
            'images' => $this->images
                ? array_map(function ($image) {
                    return url('storage/' . $image);
                }, $this->images)
                : [],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'start_process_at' => $this->start_process_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'employee' => new UserResource($this->whenLoaded('employee')),
            'hse_staff' => new UserResource($this->whenLoaded('hseStaff')),
            'observation_form' => new ObservationFormResource($this->whenLoaded('observationForm')),
        ];
    }
}
