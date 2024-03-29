<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Enum\StationCategoryEnum;
use App\Repositories\StationRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = StationCategoryEnum::toArray();

        return [
            'code' => Str::random(10),
            'name' => $this->faker->name,
            'province' => $this->faker->state,
            'regency' => $this->faker->city,
            'district' => $this->faker->streetName,
            'subdistrict' => $this->faker->streetName,
            'address' => $this->faker->address,
            'category' => $this->faker->randomElement($categories),
            'is_active' => $this->faker->boolean,
        ];
    }

    public function withoutAdministrativeUnit(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'province' => null,
                'regency' => null,
                'district' => null,
                'subdistrict' => null,
            ];
        });
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $stationRepository = new StationRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $stationRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $stationRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
