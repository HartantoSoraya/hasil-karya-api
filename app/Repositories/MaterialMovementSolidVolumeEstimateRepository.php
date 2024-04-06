<?php

namespace App\Repositories;

use App\Interfaces\MaterialMovementSolidVolumeEstimateRepositoryInterface;
use App\Models\MaterialMovementSolidVolumeEstimate;

class MaterialMovementSolidVolumeEstimateRepository implements MaterialMovementSolidVolumeEstimateRepositoryInterface
{
    public function getAllMaterialMovementSolidVolumeEstimates()
    {
        $materialMovementSolidVolumeEstimates = MaterialMovementSolidVolumeEstimate::with('station')
            ->orderBy('date', 'desc')
            ->get();

        return $materialMovementSolidVolumeEstimates;
    }

    public function create(array $data)
    {
        $materialMovementSolidVolumeEstimates = MaterialMovementSolidVolumeEstimate::create($data);
        $materialMovementSolidVolumeEstimates->code = $data['code'];
        $materialMovementSolidVolumeEstimates->date = $data['date'];
        $materialMovementSolidVolumeEstimates->station_id = $data['station_id'];
        $materialMovementSolidVolumeEstimates->solid_volume_estimate = $data['solid_volume_estimate'];
        $materialMovementSolidVolumeEstimates->save();

        return $materialMovementSolidVolumeEstimates;
    }

    public function getMaterialMovementSolidVolumeEstimateById(string $id)
    {
        $materialMovementSolidVolumeEstimates = MaterialMovementSolidVolumeEstimate::with('station')
            ->where('id', $id)
            ->first();

        return $materialMovementSolidVolumeEstimates;
    }

    public function update(array $data, string $id)
    {
        $materialMovementSolidVolumeEstimates = MaterialMovementSolidVolumeEstimate::find($id);
        $materialMovementSolidVolumeEstimates->code = $data['code'];
        $materialMovementSolidVolumeEstimates->date = $data['date'];
        $materialMovementSolidVolumeEstimates->station_id = $data['station_id'];
        $materialMovementSolidVolumeEstimates->solid_volume_estimate = $data['solid_volume_estimate'];
        $materialMovementSolidVolumeEstimates->save();

        return $materialMovementSolidVolumeEstimates;
    }

    public function delete(string $id)
    {
        $materialMovementSolidVolumeEstimates = MaterialMovementSolidVolumeEstimate::find($id);
        $materialMovementSolidVolumeEstimates->delete();

        return $materialMovementSolidVolumeEstimates;
    }

    public function generateCode(int $tryCount): string
    {
        $count = MaterialMovementSolidVolumeEstimate::withTrashed()->count() + 1 + $tryCount;
        $code = 'MMSVE-'.str_pad($count, 5, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = MaterialMovementSolidVolumeEstimate::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }
}
