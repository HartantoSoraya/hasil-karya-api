<?php

namespace Database\Factories;

use App\Repositories\MaterialMovementSolidVolumeEstimateRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaterialMovementSolidVolumeEstimateFactory extends Factory
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
            'date' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
            'solid_volume_estimate' => $this->faker->randomFloat(8, 0, 100),
            'remarks' => $this->faker->text(),
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $materialMovementSolidVolumeEstimatesRepository = new MaterialMovementSolidVolumeEstimateRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $materialMovementSolidVolumeEstimatesRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $materialMovementSolidVolumeEstimatesRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
