<?php

namespace App\Repositories;

use App\Interfaces\TruckRepositoryInterface;
use App\Models\Truck;

class TruckRepository implements TruckRepositoryInterface
{
    public function getAllTrucks()
    {
        $trucks = Truck::with('vendor')->get();
        $trucks = $trucks->sortBy('vendor.name')
            ->sortBy('brand')
            ->sortBy('model')
            ->sortBy('capacity')
            ->sortBy('production_year');

        return $trucks;
    }

    public function create($data)
    {
        $truck = new Truck();
        $truck->code = $data['code'];
        $truck->brand = $data['brand'];
        $truck->model = $data['model'];
        $truck->capacity = $data['capacity'];
        $truck->production_year = $data['production_year'];
        $truck->vendor_id = $data['vendor_id'];
        $truck->is_active = $data['is_active'];
        $truck->save();

        return $truck;
    }

    public function getTruckById($id)
    {
        $truck = Truck::find($id);

        return $truck;
    }

    public function update($data, $id)
    {
        $truck = Truck::find($id);
        $truck->code = $data['code'];
        $truck->brand = $data['brand'];
        $truck->model = $data['model'];
        $truck->capacity = $data['capacity'];
        $truck->production_year = $data['production_year'];
        $truck->vendor_id = $data['vendor_id'];
        $truck->is_active = $data['is_active'];
        $truck->save();

        return $truck;
    }

    public function updateActiveStatus($id, $status)
    {
        $truck = Truck::find($id);
        $truck->is_active = $status;
        $truck->save();

        return $truck;
    }

    public function delete($id)
    {
        $truck = Truck::find($id);
        $truck->delete();

        return $truck;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Truck::withTrashed()->count() + 1 + $tryCount;
        $code = 'TRK'.str_pad($count, 3, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Truck::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isAvailable($id): bool
    {
        $truck = Truck::find($id);

        return $truck->is_active;
    }
}
