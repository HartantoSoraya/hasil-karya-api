<?php

namespace App\Repositories;

use App\Enum\StationCategoryEnum;
use App\Interfaces\StationRepositoryInterface;
use App\Models\Station;

class StationRepository implements StationRepositoryInterface
{
    public function getAllStations(?string $type = null)
    {
        $stations = Station::query();

        if ($type == 'station') {
            $stations = $stations
                ->orWhere('category', StationCategoryEnum::QUARY)
                ->orWhere('category', StationCategoryEnum::DISPOSAL);
        } elseif ($type == 'gas_station') {
            $stations = $stations->orWhere('category', StationCategoryEnum::GAS);
        }

        $stations = $stations->orderBy('province', 'asc')
            ->orderBy('regency', 'asc')
            ->orderBy('district', 'asc')
            ->orderBy('subdistrict', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return $stations;
    }

    public function create(array $data)
    {
        $station = new Station();
        $station->code = $data['code'];
        $station->name = $data['name'];
        $station->province = $data['province'];
        $station->regency = $data['regency'];
        $station->district = $data['district'];
        $station->subdistrict = $data['subdistrict'];
        $station->address = $data['address'];
        $station->category = $data['category'];
        $station->material_id = $data['material_id'];
        $station->is_active = $data['is_active'];
        $station->save();

        return $station;
    }

    public function getStationById($id)
    {
        $station = Station::find($id);

        return $station;
    }

    public function getStationCategory()
    {
        $categories = [];

        foreach (StationCategoryEnum::toArray() as $category) {
            $categories[] = [
                'name' => $category,
            ];
        }

        return $categories;
    }

    public function update(array $data, $id)
    {
        $station = Station::find($id);
        $station->code = $data['code'];
        $station->name = $data['name'];
        $station->province = $data['province'];
        $station->regency = $data['regency'];
        $station->district = $data['district'];
        $station->subdistrict = $data['subdistrict'];
        $station->address = $data['address'];
        $station->category = $data['category'];
        $station->material_id = $data['material_id'];
        $station->is_active = $data['is_active'];
        $station->save();

        return $station;
    }

    public function updateActiveStatus($id, $status)
    {
        $station = Station::find($id);
        $station->is_active = $status;
        $station->save();

        return $station;
    }

    public function delete($id)
    {
        $station = Station::find($id);
        $station->delete();

        return $station;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Station::withTrashed()->count() + 1 + $tryCount;
        $code = 'STA'.str_pad($count, 4, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Station::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isAvailable(string $id): bool
    {
        $station = Station::find($id);

        return $station->is_active;
    }
}
