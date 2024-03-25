<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FuelLogErrorLogResource extends JsonResource
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
            'driver_id' => $this->driver_id,
            'truck_id' => $this->truck_id,
            'station_id' => $this->station_id,
            'checker_id' => $this->checker_id,
            'truck_capacity' => $this->truck_capacity,
            'observation_ratio_percentage' => $this->observation_ratio_percentage,
            'solid_ratio' => $this->solid_ratio,
            'remarks' => $this->remarks,
            'error_log' => $this->error_log,
            'created_at' => $this->created_at,
        ];
    }
}
