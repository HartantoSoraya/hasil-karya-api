<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialMovementSolidVolumeEstimateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'code' => 'required|unique:material_movement_solid_volume_estimates,code,'.$this->route('id').',id',
            'date' => 'required|date',
            'station_id' => 'required|exists:stations,id',
            'solid_volume_estimate' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ];
    }
}
