<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Repositories\HeavyVehicleRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeavyVehicleFactory extends Factory
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
            'brand' => $this->faker->word,
            'model' => $this->faker->word,
            'production_year' => $this->faker->year,
            'is_active' => $this->faker->boolean,
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $heavyVehicleRepository = new HeavyVehicleRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $heavyVehicleRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $heavyVehicleRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
