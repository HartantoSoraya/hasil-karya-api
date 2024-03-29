<?php

namespace App\Repositories;

use App\Enum\UserRoleEnum;
use App\Interfaces\TechnicalAdminRepositoryInterface;
use App\Models\TechnicalAdmin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TechnicalAdminRepository implements TechnicalAdminRepositoryInterface
{
    public function getAllTechnicalAdmins()
    {
        $technicalAdmins = TechnicalAdmin::orderBy('name', 'asc')->get();

        return $technicalAdmins;
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            $user->assignRole(UserRoleEnum::TECHNICAL_ADMIN);

            $technicalAdmin = new TechnicalAdmin();
            $technicalAdmin->user_id = $user->id;
            $technicalAdmin->code = $data['code'];
            $technicalAdmin->name = $data['name'];
            $technicalAdmin->is_active = $data['is_active'];
            $technicalAdmin->save();

            DB::commit();

            return $technicalAdmin;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function getTechnicalAdminById(string $id)
    {
        $technicalAdmin = TechnicalAdmin::find($id);

        return $technicalAdmin;
    }

    public function update(array $data, string $id)
    {
        try {
            DB::beginTransaction();

            $technicalAdmin = TechnicalAdmin::find($id);
            $technicalAdmin->name = $data['name'];
            $technicalAdmin->is_active = $data['is_active'];
            $technicalAdmin->save();

            if (isset($data['password'])) {
                $user = User::find($technicalAdmin->user_id);
                $user->password = bcrypt($data['password']);
                $user->save();
            }

            DB::commit();

            return $technicalAdmin;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function updateActiveStatus(string $id, bool $status)
    {
        $technicalAdmin = TechnicalAdmin::find($id);
        $technicalAdmin->is_active = $status;
        $technicalAdmin->save();

        return $technicalAdmin;
    }

    public function delete(string $id)
    {
        $technicalAdmin = TechnicalAdmin::find($id);
        $technicalAdmin->delete();

        return $technicalAdmin;
    }

    public function generateCode(int $tryCount): string
    {
        $count = TechnicalAdmin::withTrashed()->count() + 1 + $tryCount;
        $code = 'TA'.str_pad($count, 4, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = TechnicalAdmin::where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() == 0;
    }
}
