<?php

namespace App\Repositories;

use App\Interfaces\DriverRepositoryInterface;
use App\Models\Driver;

class DriverRepository implements DriverRepositoryInterface
{
    public function getAllDrivers()
    {
        $drivers = Driver::orderBy('name', 'asc')->get();

        return $drivers;
    }

    public function create(array $data)
    {
        $driver = new Driver();
        $driver->code = $data['code'];
        $driver->name = $data['name'];
        $driver->is_active = $data['is_active'];
        $driver->save();

        return $driver;
    }

    public function getDriverById($id)
    {
        $driver = Driver::find($id);

        return $driver;
    }

    public function update(array $data, $id)
    {
        $driver = Driver::find($id);
        $driver->code = $data['code'];
        $driver->name = $data['name'];
        $driver->is_active = $data['is_active'];
        $driver->save();

        return $driver;
    }

    public function updateActiveStatus($id, $status)
    {
        $driver = Driver::find($id);
        $driver->is_active = $status;
        $driver->save();

        return $driver;
    }

    public function delete($id)
    {
        $driver = Driver::find($id);
        $driver->delete();

        return $driver;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Driver::withTrashed()->count() + 1 + $tryCount;
        $code = 'DRV'.str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Driver::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isAvailable($id): bool
    {
        $driver = Driver::find($id);

        return $driver->is_active;
    }
}
