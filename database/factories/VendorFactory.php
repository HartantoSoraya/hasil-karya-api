<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Repositories\VendorRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
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
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'is_active' => $this->faker->boolean,
        ];
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $vendorRepository = new VendorRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $vendorRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $vendorRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
