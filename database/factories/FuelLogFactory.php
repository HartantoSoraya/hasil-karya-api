<?php

namespace Database\Factories;

use App\Enum\FuelTypeEnum;
use App\Enum\UserRoleEnum;
use App\Models\Driver;
use App\Models\GasOperator;
use App\Models\HeavyVehicle;
use App\Models\Station;
use App\Models\Truck;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\FuelLogRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
            'fuel_type' => $this->faker->randomElement(FuelTypeEnum::toArrayValue()),
            'volume' => $this->faker->randomFloat(2, 0.3, 1),
            'odometer' => $this->faker->randomFloat(2, 0.3, 1),
            'hourmeter' => $this->faker->randomFloat(2, 0.3, 1),
            'remarks' => $this->faker->text(),
        ];
    }

    public function makeForTruck()
    {
        return $this->state(function (array $attributes) {
            $truck = Truck::factory()
                ->for(Vendor::factory())
                ->create(['is_active' => true]);

            $driver = Driver::factory()->create(['is_active' => true]);

            $station = Station::factory()->create(['is_active' => true]);

            $gasOperator = GasOperator::factory()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create(['is_active' => true]);

            return [
                'truck_id' => $truck->id,
                'driver_id' => $driver->id,
                'station_id' => $station->id,
                'gas_operator_id' => $gasOperator->id,
                'hourmeter' => 0,
            ];
        });
    }

    public function makeForHeavyVehicle()
    {
        return $this->state(function (array $attributes) {
            $heavyVehicle = HeavyVehicle::factory()
                ->for(Vendor::factory())
                ->create(['is_active' => true]);

            $driver = Driver::factory()->create(['is_active' => true]);

            $station = Station::factory()->create(['is_active' => true]);

            $gasOperator = GasOperator::factory()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create(['is_active' => true]);

            return [
                'heavy_vehicle_id' => $heavyVehicle->id,
                'driver_id' => $driver->id,
                'station_id' => $station->id,
                'gas_operator_id' => $gasOperator->id,
                'odometer' => 0,
            ];
        });
    }

    public function withExpectedCode(): self
    {
        return $this->state(function (array $attributes) {
            $fuelLogRepository = new FuelLogRepository();

            $code = '';
            $tryCount = 0;
            do {
                $code = $fuelLogRepository->generateCode($tryCount);
                $tryCount++;
            } while (! $fuelLogRepository->isUniqueCode($code));

            return [
                'code' => $code,
            ];
        });
    }
}
