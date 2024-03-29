<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Http\Resources\DriverResource;
use App\Repositories\DriverRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
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
            'name' => $this->faker->name,
            'is_active' => $this->faker->boolean,
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $driverRepository = new DriverRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $driverRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $driverRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
