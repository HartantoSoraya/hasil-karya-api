<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FuelLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strval(Str::random(10)),
            'date' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            'fuel_type' => $this->faker->randomElement(['Diesel', 'Gasoline']),
            'volume' => $this->faker->randomFloat(2, 0.3, 1),
            'odometer' => $this->faker->randomFloat(2, 0.3, 1),
            'hourmeter' => $this->faker->randomFloat(2, 0.3, 1),
            'remarks' => $this->faker->text(),
        ];
    }
}
