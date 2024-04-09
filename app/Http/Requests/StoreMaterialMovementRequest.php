<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialMovementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'code' => 'required|unique:material_movements,code',
            'driver_id' => 'required|exists:drivers,id',
            'truck_id' => 'required|exists:trucks,id',
            'station_id' => 'required|exists:stations,id',
            'checker_id' => 'required|exists:checkers,id',
            'date' => 'required|date',
            'truck_capacity' => 'nullable|numeric|min:0',
            'observation_ratio' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('code')) {
            $this->merge(['code' => 'AUTO']);
        }

        if (! $this->has('date')) {
            $this->merge(['date' => now()]);
        }

        if (! $this->has('truck_capacity')) {
            $this->merge(['truck_capacity' => 0]);
        }

        if (! $this->has('observation_ratio')) {
            $this->merge(['observation_ratio' => 0]);
        }

        if (! $this->has('remarks')) {
            $this->merge(['remarks' => '']);
        }
    }
}
