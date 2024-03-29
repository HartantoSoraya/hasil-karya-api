<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\HeavyVehicle;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeavyVehicleAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_heavy_vehicle_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            HeavyVehicle::factory()->withExpectedCode()->for(Vendor::factory())->create();
        }

        $response = $this->json('GET', '/api/v1/heavy-vehicles');

        $response->assertSuccessful();
    }

    public function test_heavy_vehicle_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/heavy-vehicle', $heavyVehicle);

        $response->assertSuccessful();

        $heavyVehicle['code'] = $response['data']['code'];

        $this->assertDatabaseHas('heavy_vehicles', $heavyVehicle);
    }

    public function test_heavy_vehicle_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/heavy-vehicle/' . $heavyVehicle->id);

        $response->assertSuccessful();
    }

    public function test_heavy_vehicle_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedHeavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/heavy-vehicle/' . $heavyVehicle->id, $updatedHeavyVehicle);

        $response->assertSuccessful();

        $updatedHeavyVehicle['code'] = $response['data']['code'];

        $this->assertDatabaseHas('heavy_vehicles', $updatedHeavyVehicle);
    }

    public function test_heavy_vehicle_api_call_update_with_existing_code_in_same_heavy_vehicle_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedHeavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->make(['code' => $heavyVehicle->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/heavy-vehicle/' . $heavyVehicle->id, $updatedHeavyVehicle);

        $response->assertSuccessful();

        $this->assertDatabaseHas('heavy_vehicles', $updatedHeavyVehicle);
    }

    public function test_heavy_vehicle_api_call_update_with_existing_code_in_different_heavy_vehicle_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingHeavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $newHeavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedHeavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->make(['code' => $existingHeavyVehicle->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/heavy-vehicle/' . $newHeavyVehicle->id, $updatedHeavyVehicle);

        $response->assertStatus(422);
    }

    public function test_heavy_vehicle_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/heavy-vehicle/active/' . $heavyVehicle->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('heavy_vehicles', ['id' => $heavyVehicle->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/heavy-vehicle/active/' . $heavyVehicle->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('heavy_vehicles', ['id' => $heavyVehicle->id, 'is_active' => true]);
    }

    public function test_heavy_vehicle_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('DELETE', '/api/v1/heavy-vehicle/' . $heavyVehicle->id);

        $response->assertSuccessful();

        $heavyVehicle = $heavyVehicle->toArray();
        $heavyVehicle = Arr::except($heavyVehicle, ['deleted_at', 'created_at', 'updated_at']);

        $this->assertSoftDeleted('heavy_vehicles', $heavyVehicle);
    }

    public function test_heavy_vehicle_api_call_check_availability_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $heavyVehicle = HeavyVehicle::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/heavy-vehicle/check-availability/' . $heavyVehicle->id);

        $response->assertSuccessful();
    }
}
