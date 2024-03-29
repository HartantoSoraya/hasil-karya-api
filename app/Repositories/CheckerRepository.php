<?php

namespace App\Repositories;

use App\Enum\UserRoleEnum;
use App\Interfaces\CheckerRepositoryInterface;
use App\Models\Checker;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckerRepository implements CheckerRepositoryInterface
{
    public function getAllCheckers()
    {
        $checkers = Checker::orderBy('name', 'asc')->get();

        return $checkers;
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->save();
            $user->assignRole(UserRoleEnum::CHECKER);

            $checker = new Checker();
            $checker->user_id = $user->id;
            $checker->code = $data['code'];
            $checker->name = $data['name'];
            $checker->is_active = $data['is_active'];
            $checker->save();

            DB::commit();

            return $checker;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function getCheckerById($id)
    {
        $checker = Checker::find($id);

        return $checker;
    }

    public function update(array $data, $id)
    {
        try {
            DB::beginTransaction();

            $checker = Checker::find($id);
            $checker->code = $data['code'];
            $checker->name = $data['name'];
            $checker->is_active = $data['is_active'];
            $checker->save();

            if (isset($data['password'])) {
                $user = User::find($checker->user_id);
                $user->password = bcrypt($data['password']);
                $user->save();
            }

            DB::commit();

            return $checker;
        } catch (\Exception $e) {
            DB::rollback();

            return $e;
        }
    }

    public function updateActiveStatus($id, $status)
    {
        $checker = Checker::find($id);
        $checker->is_active = $status;
        $checker->save();

        return $checker;
    }

    public function delete($id)
    {
        $checker = Checker::find($id);
        $checker->delete();

        return $checker;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Checker::withTrashed()->count() + 1 + $tryCount;
        $code = 'CHK'.str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Checker::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() == 0;
    }
}
