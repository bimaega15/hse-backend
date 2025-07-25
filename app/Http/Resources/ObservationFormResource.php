<?php
// app/Http/Resources/ObservationFormResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ObservationFormResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'at_risk_behavior' => $this->at_risk_behavior,
            'nearmiss_incident' => $this->nearmiss_incident,
            'informasi_risk_mgmt' => $this->informasi_risk_mgmt,
            'sim_k3' => $this->sim_k3,
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
