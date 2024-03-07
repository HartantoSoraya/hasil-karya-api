<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialMovementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'code' => 'required|unique:material_movements,code,'.$this->route('id').',id',
            'driver_id' => 'required|exists:drivers,id',
            'truck_id' => 'required|exists:trucks,id',
            'station_id' => 'required|exists:stations,id',
            'checker_id' => 'required|exists:checkers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric',
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

        if (! $this->has('amount')) {
            $this->merge(['amount' => -1]);
        }

        if (! $this->has('remarks')) {
            $this->merge(['remarks' => '']);
        }
    }
}
