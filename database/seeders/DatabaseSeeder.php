<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AccountSeeder::class,

            ClientSeeder::class,
            VendorSeeder::class,
            MaterialSeeder::class,
            StationSeeder::class,

            TruckSeeder::class,
            HeavyVehicleSeeder::class,
            DriverSeeder::class,

            VehicleRentalRecordSeeder::class,

            TechnicalAdminSeeder::class,
            GasOperatorSeeder::class,
            CheckerSeeder::class,

            MaterialMovementSeeder::class,

            ProjectSeeder::class,

            NotificationRecepientSeeder::class,
        ]);
    }
}
