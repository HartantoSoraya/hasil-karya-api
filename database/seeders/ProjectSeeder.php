<?php

namespace Database\Seeders;

use App\Models\Checker;
use App\Models\Client;
use App\Models\Driver;
use App\Models\GasOperator;
use App\Models\HeavyVehicle;
use App\Models\Project;
use App\Models\Station;
use App\Models\TechnicalAdmin;
use App\Models\Truck;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $project = Project::factory()
                ->for(Client::inRandomOrder()->first())
                ->withExpectedCode()
                ->create();

            $project->drivers()->attach(Driver::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->trucks()->attach(Truck::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->heavyVehicles()->attach(HeavyVehicle::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->stations()->attach(Station::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->checkers()->attach(Checker::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->technicalAdmins()->attach(TechnicalAdmin::inRandomOrder()->limit(mt_rand(0, 3))->get());
            $project->gasOperators()->attach(GasOperator::inRandomOrder()->limit(mt_rand(0, 3))->get());
        }
    }
}
