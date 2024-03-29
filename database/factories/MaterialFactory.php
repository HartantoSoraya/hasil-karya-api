<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Repositories\MaterialRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
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
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $materialRepository = new MaterialRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $materialRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $materialRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
