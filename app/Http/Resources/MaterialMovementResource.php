<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialMovementResource extends JsonResource
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
            'driver' => DriverResource::make($this->whenLoaded('driver')),
            'truck' => TruckResource::make($this->whenLoaded('truck')),
            'station' => StationResource::make($this->whenLoaded('station')),
            'checker' => CheckerResource::make($this->whenLoaded('checker')),
            'date' => $this->date,
            'formatted_date' => $this->formatted_date,
            'truck_capacity' => $this->truck_capacity,
            'observation_ratio' => $this->observation_ratio,
            'solid_ratio' => $this->solid_ratio,
            'solid_volume_estimate' => $this->solid_volume_estimate,
            'remarks' => $this->remarks,
        ];
    }
}
