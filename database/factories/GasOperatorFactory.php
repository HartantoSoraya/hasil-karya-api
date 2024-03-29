<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Repositories\GasOperatorRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class GasOperatorFactory extends Factory
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

    public function withCredentials(): GasOperatorFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => $this->faker->unique()->safeEmail,
                'password' => 'password',
            ];
        });
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $gasOperatorRepository = new GasOperatorRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $gasOperatorRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $gasOperatorRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
