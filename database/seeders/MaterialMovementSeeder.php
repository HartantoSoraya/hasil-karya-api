<?php

namespace Database\Seeders;

use App\Enum\StationCategoryEnum;
use App\Models\Checker;
use App\Models\Driver;
use App\Models\MaterialMovement;
use App\Models\Station;
use App\Models\Truck;
use DateTime;
use Illuminate\Database\Seeder;

class MaterialMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 400; $i++) {
            $start_date = now()->startOfYear();
            $current_date = new DateTime();
            $interval = $current_date->diff($start_date);
            $days_difference = $interval->days;

            $driver = Driver::inRandomOrder()->first();
            $truck = Truck::inRandomOrder()->first();
            $station = Station::where('category', '!=', StationCategoryEnum::GAS->value)->inRandomOrder()->first();
            $checker = Checker::inRandomOrder()->first();
            $date = now()->startOfYear()->addDays(rand(0, $days_difference))->toDateTimeString();
            $truckCapacity = $truck->capacity;
            $observationRatio = $truckCapacity * (rand(3, 10) / 10);
            $solidRatio = rand(3, 10) / 10;
            $solidVolumeEstimate = $observationRatio * $solidRatio;

            MaterialMovement::factory()->create([
                'driver_id' => $driver->id,
                'truck_id' => $truck->id,
                'station_id' => $station->id,
                'checker_id' => $checker->id,
                'date' => $date,
                'truck_capacity' => $truckCapacity,
                'observation_ratio' => $observationRatio,
                'solid_ratio' => $solidRatio,
                'solid_volume_estimate' => $solidVolumeEstimate,
                'remarks' => '',
            ]);
        }
    }
}
