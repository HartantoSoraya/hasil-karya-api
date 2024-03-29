<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\GasOperator;
use App\Models\HeavyVehicle;
use App\Models\Station;
use App\Models\Truck;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FuelLogAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_fuel_log_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Truck::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            HeavyVehicle::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Driver::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Station::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            GasOperator::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create();
        }

        if (mt_rand(0, 1)) {
            FuelLog::factory()
                ->withExpectedCode()
                ->makeForTruck()
                ->create();
        } else {
            FuelLog::factory()
                ->withExpectedCode()
                ->makeForHeavyVehicle()
                ->create();
        }

        $response = $this->getJson('/api/v1/fuel-logs');

        $response->assertSuccessful();
    }

    public function test_fuel_log_api_call_create_without_sending_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make()
            ->toArray();
        unset($fuelLog['code']);

        $response = $this->postJson('/api/v1/fuel-log/truck', $fuelLog);

        $response->assertSuccessful();

        $this->assertDatabaseHas('fuel_logs', $fuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make()
            ->toArray();
        unset($fuelLog['code']);

        $response = $this->postJson('/api/v1/fuel-log/heavy-vehicle', $fuelLog);

        $response->assertSuccessful();

        $this->assertDatabaseHas('fuel_logs', $fuelLog);
    }

    public function test_fuel_log_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/truck', $fuelLog);

        $response->assertSuccessful();

        $fuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $fuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/heavy-vehicle', $fuelLog);

        $response->assertSuccessful();

        $fuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $fuelLog);
    }

    public function test_fuel_log_api_call_create_with_auto_code_and_wrong_formatted_date_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO', 'date' => strval(mt_rand(1000000000, 9999999999))])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/truck', $fuelLog);

        $response->assertStatus(422);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO', 'date' => strval(mt_rand(1000000000, 9999999999))])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/heavy-vehicle', $fuelLog);

        $response->assertStatus(422);
    }

    public function test_fuel_log_api_call_create_with_auto_code_and_negative_numeric_value_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO', 'volume' => -1])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/truck', $fuelLog);

        $response->assertStatus(422);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO', 'volume' => -1])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/heavy-vehicle', $fuelLog);

        $response->assertStatus(422);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO', 'odometer' => -1])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/truck', $fuelLog);

        $response->assertStatus(422);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO', 'hourmeter' => -1])
            ->toArray();

        $response = $this->postJson('/api/v1/fuel-log/heavy-vehicle', $fuelLog);
    }

    public function test_fuel_log_api_call_create_with_auto_code_by_gas_operator_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->postJson('/api/v1/gas-operator/fuel-log/truck/store', $fuelLog);

        $response->assertSuccessful();

        $fuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $fuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->postJson('/api/v1/gas-operator/fuel-log/heavy-vehicle/store', $fuelLog);

        $response->assertSuccessful();

        $fuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $fuelLog);
    }

    public function test_fuel_log_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeforTruck()
            ->create(['hourmeter' => 0]);

        $response = $this->getJson('/api/v1/fuel-log/' . $fuelLog->id);

        $response->assertSuccessful();

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $response = $this->getJson('/api/v1/fuel-log/' . $fuelLog->id);

        $response->assertSuccessful();
    }

    public function test_fuel_log_api_call_read_fuel_types_expect_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $response = $this->getJson('/api/v1/fuel-log/read/fuel-types');

        $response->assertSuccessful();
    }

    public function test_fuel_log_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->create(['hourmeter' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => 'AUTO'])
            ->toArray(['hourmeter' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/truck/' . $fuelLog->id, $updatedFuelLog);

        $response->assertSuccessful();

        $updatedFuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $updatedFuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => 'AUTO'])
            ->toArray(['odometer' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/heavy-vehicle/' . $fuelLog->id, $updatedFuelLog);

        $response->assertSuccessful();

        $updatedFuelLog['code'] = $response['data']['code'];

        $this->assertDatabaseHas('fuel_logs', $updatedFuelLog);
    }

    public function test_fuel_log_api_call_update_with_existing_code_in_same_fuel_log_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->create(['hourmeter' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => $fuelLog->code])
            ->toArray(['hourmeter' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/truck/' . $fuelLog->id, $updatedFuelLog);

        $response->assertSuccessful();

        $this->assertDatabaseHas('fuel_logs', $updatedFuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => $fuelLog->code])
            ->toArray(['odometer' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/heavy-vehicle/' . $fuelLog->id, $updatedFuelLog);

        $response->assertSuccessful();

        $this->assertDatabaseHas('fuel_logs', $updatedFuelLog);
    }

    public function test_fuel_log_api_call_update_with_existing_code_in_different_fuel_log_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingFuelLog = FuelLog::factory()
            ->makeForTruck()
            ->create(['hourmeter' => 0]);

        $newFuelLog = FuelLog::factory()
            ->makeForTruck()
            ->create(['hourmeter' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForTruck()
            ->make(['code' => $existingFuelLog->code])
            ->toArray(['hourmeter' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/truck/' . $newFuelLog->id, $updatedFuelLog);

        $response->assertStatus(422);

        $existingFuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $newFuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $updatedFuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->make(['code' => $existingFuelLog->code])
            ->toArray(['odometer' => 0]);

        $response = $this->json('POST', '/api/v1/fuel-log/heavy-vehicle/' . $newFuelLog->id, $updatedFuelLog);

        $response->assertStatus(422);
    }

    public function test_fuel_log_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $fuelLog = FuelLog::factory()
            ->makeForTruck()
            ->create(['hourmeter' => 0]);

        $response = $this->json('DELETE', '/api/v1/fuel-log/' . $fuelLog->id);

        $response->assertSuccessful();

        $fuelLog = $fuelLog->toArray();
        $fuelLog = Arr::except($fuelLog, ['created_at', 'updated_at', 'deleted_at']);
        $this->assertSoftDeleted('fuel_logs', $fuelLog);

        $fuelLog = FuelLog::factory()
            ->makeForHeavyVehicle()
            ->create(['odometer' => 0]);

        $response = $this->json('DELETE', '/api/v1/fuel-log/' . $fuelLog->id);

        $response->assertSuccessful();

        $fuelLog = $fuelLog->toArray();
        $fuelLog = Arr::except($fuelLog, ['created_at', 'updated_at', 'deleted_at']);
        $this->assertSoftDeleted('fuel_logs', $fuelLog);
    }
}
