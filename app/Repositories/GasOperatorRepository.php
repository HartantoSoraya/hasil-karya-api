<?php

namespace App\Repositories;

use App\Enum\UserRoleEnum;
use App\Interfaces\GasOperatorRepositoryInterface;
use App\Models\GasOperator;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GasOperatorRepository implements GasOperatorRepositoryInterface
{
    public function getAllGasOperators()
    {
        $gasOperators = GasOperator::orderBy('name', 'asc')->get();

        return $gasOperators;
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            $user->assignRole(UserRoleEnum::GAS_OPERATOR);

            $gasOperator = new GasOperator();
            $gasOperator->user_id = $user->id;
            $gasOperator->code = $data['code'];
            $gasOperator->name = $data['name'];
            $gasOperator->is_active = $data['is_active'];
            $gasOperator->save();

            DB::commit();

            return $gasOperator;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function getGasOperatorById(string $id)
    {
        $gasOperator = GasOperator::find($id);

        return $gasOperator;
    }

    public function update(array $data, string $id)
    {
        try {
            DB::beginTransaction();

            $gasOperator = GasOperator::find($id);
            $gasOperator->code = $data['code'];
            $gasOperator->name = $data['name'];
            $gasOperator->is_active = $data['is_active'];
            $gasOperator->save();

            if (isset($data['password'])) {
                $user = User::find($gasOperator->user_id);
                $user->password = bcrypt($data['password']);
                $user->save();
            }

            DB::commit();

            return $gasOperator;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function updateActiveStatus(string $id, bool $status)
    {
        $gasOperator = GasOperator::find($id);
        $gasOperator->is_active = $status;
        $gasOperator->save();

        return $gasOperator;
    }

    public function delete(string $id)
    {
        $gasOperator = GasOperator::find($id);
        $gasOperator->delete();

        return $gasOperator;
    }

    public function generateCode(int $tryCount): string
    {
        $count = GasOperator::withTrashed()->count() + 1 + $tryCount;
        $code = 'GASOP'.str_pad($count, 4, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = GasOperator::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() == 0;
    }
}
