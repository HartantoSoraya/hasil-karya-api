<?php

namespace Tests\Feature;

use App\Enum\AggregateFunctionEnum;
use App\Enum\DatePeriodEnum;
use App\Enum\StationCategoryEnum;
use App\Enum\UserRoleEnum;
use App\Models\Checker;
use App\Models\Driver;
use App\Models\MaterialMovement;
use App\Models\Station;
use App\Models\Truck;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialMovementAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_material_movement_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->withExpectedCode()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovements = [];
        for ($i = 0; $i < 5; $i++) {
            $materialMovements[] = MaterialMovement::factory()
                ->withExpectedCode()
                ->for(Driver::factory()->create(['is_active' => true]), 'driver')
                ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
                ->for(Station::factory()->create(['is_active' => true]), 'station')
                ->for($checker, 'checker')
                ->create();
        }

        $response = $this->json('GET', '/api/v1/material-movements');

        $response->assertSuccessful();

        foreach ($materialMovements as $materialMovement) {
            $materialMovement = $materialMovement->toArray();
            $materialMovement = Arr::except($materialMovement, ['created_at', 'updated_at', 'deleted_at']);
            $this->assertDatabaseHas('material_movements', $materialMovement);
        }
    }

    public function test_material_movement_api_call_create_without_sending_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make()
            ->toArray();
        unset($materialMovement['code']);

        $response = $this->json('POST', '/api/v1/material-movement', $materialMovement);

        $response->assertSuccessful();

        $materialMovement['code'] = $response['data']['code'];
        unset($materialMovement['observation_ratio_number']);
        unset($materialMovement['solid_ratio']);
        unset($materialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $materialMovement);
    }

    public function test_material_movement_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement', $materialMovement);

        $response->assertSuccessful();

        $materialMovement['code'] = $response['data']['code'];
        unset($materialMovement['observation_ratio_number']);
        unset($materialMovement['solid_ratio']);
        unset($materialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $materialMovement);
    }

    public function test_material_movement_api_call_create_with_auto_code_and_wrong_formatted_date_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => 'AUTO', 'date' => strval(mt_rand(1000000000, 9999999999))])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement', $materialMovement);

        $response->assertStatus(422);
    }

    public function test_material_movement_api_call_create_with_auto_code_and_negative_observation_ratio_percentage_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => 'AUTO', 'observation_ratio_percentage' => mt_rand(-1000, -2)])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement', $materialMovement);

        $response->assertStatus(422);
    }

    public function test_material_movement_api_call_create_with_auto_code_by_checker_user_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()->for(
            User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
        )->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/checker/material-movement/store', $materialMovement);

        $response->assertSuccessful();

        $materialMovement['code'] = $response['data']['code'];
        unset($materialMovement['observation_ratio_number']);
        unset($materialMovement['solid_ratio']);
        unset($materialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $materialMovement);
    }

    public function test_material_movement_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $response = $this->json('GET', '/api/v1/material-movement/' . $materialMovement->id);

        $response->assertSuccessful();
    }

    public function test_material_movement_api_call_get_statistic_truck_per_day_by_station()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $statisticTypes = AggregateFunctionEnum::toArrayValue();
        $datePeriods = DatePeriodEnum::toArrayValue();
        $stationCategories = [StationCategoryEnum::QUARY->value, StationCategoryEnum::DISPOSAL->value];

        foreach ($statisticTypes as $statisticType) {
            foreach ($datePeriods as $datePeriod) {
                foreach ($stationCategories as $stationCategory) {
                    $response = $this->json('GET', '/api/v1/material-movements/read/statistic-truck-per-day-by-station', [
                        'statistic_type' => $statisticType,
                        'date_type' => $datePeriod,
                        'station_category' => $stationCategory,
                    ]);

                    $response->assertSuccessful();
                }
            }
        }
    }

    public function test_material_movement_api_call_get_statistic_ritage_per_day_by_station()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $statisticTypes = AggregateFunctionEnum::toArrayValue();
        $datePeriods = DatePeriodEnum::toArrayValue();
        $stationCategories = [StationCategoryEnum::QUARY->value, StationCategoryEnum::DISPOSAL->value];

        foreach ($statisticTypes as $statisticType) {
            foreach ($datePeriods as $datePeriod) {
                foreach ($stationCategories as $stationCategory) {
                    $response = $this->json('GET', '/api/v1/material-movements/read/statistic-ritage-per-day-by-station', [
                        'statistic_type' => $statisticType,
                        'date_type' => $datePeriod,
                        'station_category' => $stationCategory,
                    ]);

                    $response->assertSuccessful();
                }
            }
        }
    }

    public function test_material_movement_api_call_get_statistic_measurement_volume_by_station()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $statisticTypes = AggregateFunctionEnum::toArrayValue();
        $datePeriods = DatePeriodEnum::toArrayValue();
        $stationCategories = [StationCategoryEnum::QUARY->value, StationCategoryEnum::DISPOSAL->value];

        foreach ($statisticTypes as $statisticType) {
            foreach ($datePeriods as $datePeriod) {
                foreach ($stationCategories as $stationCategory) {
                    $response = $this->json('GET', '/api/v1/material-movements/read/statistic-measurement-volume-by-station', [
                        'statistic_type' => $statisticType,
                        'date_type' => $datePeriod,
                        'station_category' => $stationCategory,
                    ]);

                    $response->assertSuccessful();
                }
            }
        }
    }

    public function test_material_movement_api_call_get_statistic_ritage_volume_by_station()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $statisticTypes = AggregateFunctionEnum::toArrayValue();
        $datePeriods = DatePeriodEnum::toArrayValue();
        $stationCategories = [StationCategoryEnum::QUARY->value, StationCategoryEnum::DISPOSAL->value];

        foreach ($statisticTypes as $statisticType) {
            foreach ($datePeriods as $datePeriod) {
                foreach ($stationCategories as $stationCategory) {
                    $response = $this->json('GET', '/api/v1/material-movements/read/statistic-ritage-volume-by-station', [
                        'statistic_type' => $statisticType,
                        'date_type' => $datePeriod,
                        'station_category' => $stationCategory,
                    ]);

                    $response->assertSuccessful();
                }
            }
        }
    }

    public function test_material_movement_api_call_get_ratio_measurement_by_ritage()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $statisticTypes = AggregateFunctionEnum::toArrayValue();
        $datePeriods = DatePeriodEnum::toArrayValue();
        $stationCategories = [StationCategoryEnum::QUARY->value, StationCategoryEnum::DISPOSAL->value];

        foreach ($statisticTypes as $statisticType) {
            foreach ($datePeriods as $datePeriod) {
                foreach ($stationCategories as $stationCategory) {
                    $response = $this->json('GET', '/api/v1/material-movements/read/ratio-measurement-by-ritage', [
                        'statistic_type' => $statisticType,
                        'date_type' => $datePeriod,
                        'station_category' => $stationCategory,
                    ]);

                    $response->assertSuccessful();
                }
            }
        }
    }

    public function test_material_movement_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $updatedMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement/' . $materialMovement->id, $updatedMaterialMovement);

        $response->assertSuccessful();

        $updatedMaterialMovement['code'] = $response['data']['code'];
        unset($updatedMaterialMovement['observation_ratio_number']);
        unset($updatedMaterialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $updatedMaterialMovement);
    }

    public function test_material_movement_api_call_update_with_existing_code_by_technical_admin_user_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()->for(
            User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
        )->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $updatedMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => $materialMovement->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/technical-admin/material-movement/update/' . $materialMovement->id, $updatedMaterialMovement);

        $response->assertSuccessful();

        unset($updatedMaterialMovement['observation_ratio_number']);
        unset($updatedMaterialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $updatedMaterialMovement);
    }

    public function test_material_movement_api_call_update_with_existing_code_in_same_material_movement_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $updatedMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => $materialMovement->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement/' . $materialMovement->id, $updatedMaterialMovement);

        $response->assertSuccessful();

        unset($updatedMaterialMovement['observation_ratio_number']);
        unset($updatedMaterialMovement['solid_ratio']);
        unset($updatedMaterialMovement['solid_volume_estimate']);

        $this->assertDatabaseHas('material_movements', $updatedMaterialMovement);
    }

    public function test_material_movement_api_call_update_with_existing_code_in_different_material_movement_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $existingMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $newMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $updatedMaterialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->make(['code' => $existingMaterialMovement->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/material-movement/' . $newMaterialMovement->id, $updatedMaterialMovement);

        $response->assertStatus(422);
    }

    public function test_material_movement_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $materialMovement = MaterialMovement::factory()
            ->for(Driver::factory()->create(['is_active' => true]), 'driver')
            ->for(Truck::factory()->for(Vendor::factory())->create(['is_active' => true]), 'truck')
            ->for(Station::factory()->create(['is_active' => true]), 'station')
            ->for($checker, 'checker')
            ->create();

        $response = $this->json('DELETE', '/api/v1/material-movement/' . $materialMovement->id);

        $response->assertSuccessful();

        $materialMovement = $materialMovement->toArray();
        $materialMovement = Arr::except($materialMovement, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('material_movements', $materialMovement);
    }
}
