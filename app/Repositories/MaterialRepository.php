<?php

namespace App\Repositories;

use App\Interfaces\MaterialRepositoryInterface;
use App\Models\Material;

class MaterialRepository implements MaterialRepositoryInterface
{
    public function getAllMaterials()
    {
        $materials = Material::orderBy('name', 'asc')->get();

        return $materials;
    }

    public function create(array $data)
    {
        $material = new Material();
        $material->code = $data['code'];
        $material->name = $data['name'];
        $material->save();

        return $material;
    }

    public function getMaterialById(string $id)
    {
        $material = Material::find($id);

        return $material;
    }

    public function update(array $data, string $id)
    {
        $material = Material::find($id);
        $material->code = $data['code'];
        $material->name = $data['name'];
        $material->save();

        return $material;
    }

    public function delete(string $id)
    {
        $material = Material::find($id);
        $material->delete();

        return $material;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Material::withTrashed()->count() + $tryCount;
        $code = 'MATERIAL'.str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Material::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }
}
