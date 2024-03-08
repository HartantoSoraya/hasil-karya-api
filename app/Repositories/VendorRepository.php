<?php

namespace App\Repositories;

use App\Interfaces\VendorRepositoryInterface;
use App\Models\Vendor;

class VendorRepository implements VendorRepositoryInterface
{
    public function getAllVendors()
    {
        $vendors = Vendor::orderBy('name', 'asc')->get();

        return $vendors;
    }

    public function create(array $data)
    {
        $vendor = new Vendor();
        $vendor->code = $data['code'];
        $vendor->name = $data['name'];
        $vendor->address = $data['address'];
        $vendor->phone = $data['phone'];
        $vendor->save();

        return $vendor;
    }

    public function getVendorById($id)
    {
        $vendor = Vendor::find($id);

        return $vendor;
    }

    public function update(array $data, $id)
    {
        $vendor = Vendor::find($id);
        $vendor->code = $data['code'];
        $vendor->name = $data['name'];
        $vendor->address = $data['address'];
        $vendor->phone = $data['phone'];
        $vendor->save();

        return $vendor;
    }

    public function delete($id)
    {
        $vendor = Vendor::find($id);
        $vendor->delete();

        return $vendor;
    }

    public function generateCode(int $tryCount): string
    {
        $code = 'VENDOR' . str_pad($tryCount, 3, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Vendor::where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() === 0;
    }
}
