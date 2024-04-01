<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaterialMovementErrorLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::random(10),
            'driver_id' => strval($this->faker->uuid),
            'truck_id' => strval($this->faker->uuid),
            'station_id' => strval($this->faker->uuid),
            'checker_id' => strval($this->faker->uuid),
            'date' => strval($this->faker->dateTimeThisYear()->format('Y-m-d H:i:s')),
            'truck_capacity' => strval($this->faker->numberBetween(5, 30)),
            'observation_ratio' => strval($this->faker->numberBetween(5, 30)),
            'solid_ratio' => strval($this->faker->randomFloat(2, 0.5, 1.2)),
            'remarks' => strval($this->faker->text),
            'error_log' => strval($this->faker->text),
        ];
    }
}
