<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\GasOperator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class GasOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first())
            ->create(['email' => 'gas_operator@cvhasilkarya.co.id']);

        GasOperator::factory()->for($user)->create(['is_active' => true]);
    }
}
