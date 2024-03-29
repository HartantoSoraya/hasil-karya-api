<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Repositories\MaterialMovementRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $truck_capacity = random_int(5, 20);
        $observation_ratio_percentage = $this->faker->randomFloat(2, 0.3, 1);
        $observation_ratio_number = $truck_capacity * $observation_ratio_percentage;
        $solid_ratio = $this->faker->randomFloat(4, 0.0, 1);
        $solid_volume_estimate = $observation_ratio_number * $solid_ratio;
        $ratio_measurement_ritage = $solid_volume_estimate / $observation_ratio_number;

        return [
            'code' => Str::random(10),
            'date' => $this->faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            'truck_capacity' => random_int(5, 20),
            'observation_ratio_percentage' => $observation_ratio_percentage,
            'observation_ratio_number' => $observation_ratio_number,
            'solid_ratio' => $solid_ratio,
            'solid_volume_estimate' => $solid_volume_estimate,
            'ratio_measurement_ritage' => $ratio_measurement_ritage,

            'remarks' => $this->faker->text(),
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $materialMovementRepository = new MaterialMovementRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $materialMovementRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $materialMovementRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
