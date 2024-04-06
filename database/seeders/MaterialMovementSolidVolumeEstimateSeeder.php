<?php

namespace Database\Seeders;

use App\Enum\StationCategoryEnum;
use App\Models\MaterialMovement;
use App\Models\MaterialMovementSolidVolumeEstimate;
use App\Models\Station;
use Illuminate\Database\Seeder;

class MaterialMovementSolidVolumeEstimateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = Station::where('category', '!=', StationCategoryEnum::GAS->value)->get();
        foreach ($stations as $station) {
            $date = now()->toDateTimeString();

            $materialMovementObservationTotal = MaterialMovement::where('station_id', $station->id)
                ->where('date', '<=', $date)
                ->sum('observation_ratio');

            MaterialMovementSolidVolumeEstimate::factory()
                ->withExpectedCode()
                ->create([
                    'station_id' => $station->id,
                    'date' => $date,
                    'solid_volume_estimate' => $materialMovementObservationTotal * mt_rand(80, 130) / 100,
                ]);
        }
    }
}
