<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\Checker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CheckerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first())
            ->create(['email' => 'checker@cvhasilkarya.co.id']);

        Checker::factory()->for($user)->create(['is_active' => true]);
    }
}
