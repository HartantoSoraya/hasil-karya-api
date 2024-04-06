<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialMovementSolidVolumeEstimateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'date' => $this->date,
            'solid_volume_estimate' => $this->solid_volume_estimate,
            'remarks' => $this->remarks,
        ];
    }
}
