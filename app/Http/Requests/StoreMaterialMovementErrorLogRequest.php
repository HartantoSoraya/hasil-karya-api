<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialMovementErrorLogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'code' => 'nullable|string',
            'driver_id' => 'nullable|string',
            'truck_id' => 'nullable|string',
            'station_id' => 'nullable|string',
            'checker_id' => 'nullable|string',
            'date' => 'nullable|string',
            'truck_capacity' => 'nullable|string',
            'observation_ratio' => 'nullable|string',
            'solid_ratio' => 'nullable|string',
            'remarks' => 'nullable|string',
            'error_log' => 'required|string',
        ];
    }

    public function prepareForValidation()
    {
        if (! $this->has('code')) {
            $this->merge([
                'code' => null,
            ]);
        }

        if (! $this->has('driver_id')) {
            $this->merge([
                'driver_id' => null,
            ]);
        }

        if (! $this->has('truck_id')) {
            $this->merge([
                'truck_id' => null,
            ]);
        }

        if (! $this->has('station_id')) {
            $this->merge([
                'station_id' => null,
            ]);
        }

        if (! $this->has('checker_id')) {
            $this->merge([
                'checker_id' => null,
            ]);
        }

        if (! $this->has('date')) {
            $this->merge([
                'date' => null,
            ]);
        }

        if (! $this->has('truck_capacity')) {
            $this->merge([
                'truck_capacity' => null,
            ]);
        }

        if (! $this->has('observation_ratio')) {
            $this->merge([
                'observation_ratio' => null,
            ]);
        }

        if (! $this->has('solid_ratio')) {
            $this->merge([
                'solid_ratio' => null,
            ]);
        }

        if (! $this->has('remarks')) {
            $this->merge([
                'remarks' => null,
            ]);
        }
    }
}
