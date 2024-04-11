<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appname = config('app.name');

        User::create([
            'email' => 'admin@'.str_replace(' ', '', strtolower($appname)).'.co.id',
            'password' => bcrypt('password'),
        ])->assignRole(UserRoleEnum::ADMIN->value);
    }
}
