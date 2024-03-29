<?php

namespace App\Repositories;

use App\Interfaces\HeavyVehicleRepositoryInterface;
use App\Models\HeavyVehicle;

class HeavyVehicleRepository implements HeavyVehicleRepositoryInterface
{
    public function getAllHeavyVehicles()
    {
        $heavyVehicles = HeavyVehicle::with('vendor')->get();
        $heavyVehicles = $heavyVehicles->sortBy('vendor.name')
            ->sortBy('brand')
            ->sortBy('model')
            ->sortBy('production_year');

        return $heavyVehicles;
    }

    public function create(array $data)
    {
        $heavyVehicle = new HeavyVehicle();
        $heavyVehicle->code = $data['code'];
        $heavyVehicle->brand = $data['brand'];
        $heavyVehicle->model = $data['model'];
        $heavyVehicle->production_year = $data['production_year'];
        $heavyVehicle->vendor_id = $data['vendor_id'];
        $heavyVehicle->is_active = $data['is_active'];
        $heavyVehicle->save();

        return $heavyVehicle;
    }

    public function getHeavyVehicleById(string $id)
    {
        $heavyVehicle = HeavyVehicle::find($id);

        return $heavyVehicle;
    }

    public function update(array $data, string $id)
    {
        $heavyVehicle = HeavyVehicle::find($id);
        $heavyVehicle->code = $data['code'];
        $heavyVehicle->brand = $data['brand'];
        $heavyVehicle->model = $data['model'];
        $heavyVehicle->production_year = $data['production_year'];
        $heavyVehicle->vendor_id = $data['vendor_id'];
        $heavyVehicle->is_active = $data['is_active'];
        $heavyVehicle->save();

        return $heavyVehicle;
    }

    public function updateActiveStatus(string $id, bool $status)
    {
        $heavyVehicle = HeavyVehicle::find($id);
        $heavyVehicle->is_active = $status;
        $heavyVehicle->save();

        return $heavyVehicle;
    }

    public function delete(string $id)
    {
        $heavyVehicle = HeavyVehicle::find($id);
        $heavyVehicle->delete();

        return $heavyVehicle;
    }

    public function generateCode(int $tryCount): string
    {
        $count = HeavyVehicle::withTrashed()->count() + 1 + $tryCount;
        $code = 'HV'.str_pad($count, 4, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = HeavyVehicle::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isAvailable($id): bool
    {
        $heavyVehicle = HeavyVehicle::find($id);

        return $heavyVehicle->is_active;
    }
}
