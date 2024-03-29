<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Truck;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TruckAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_truck_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Truck::factory()->withExpectedCode()->for(Vendor::factory())->create();
        }

        $response = $this->json('GET', '/api/v1/trucks');

        $response->assertSuccessful();
    }

    public function test_truck_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/truck', $truck);

        $response->assertSuccessful();

        $truck['code'] = $response['data']['code'];

        $this->assertDatabaseHas('trucks', $truck);
    }

    public function test_truck_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/truck/' . $truck->id);

        $response->assertSuccessful();
    }

    public function test_truck_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedTruck = Truck::factory()
            ->for(Vendor::factory())
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/truck/' . $truck->id, $updatedTruck);

        $response->assertSuccessful();

        $updatedTruck['code'] = $response['data']['code'];

        $this->assertDatabaseHas('trucks', $updatedTruck);
    }

    public function test_truck_api_call_update_with_existing_code_in_same_truck_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedTruck = Truck::factory()
            ->for(Vendor::factory())
            ->make(['code' => $truck->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/truck/' . $truck->id, $updatedTruck);

        $response->assertSuccessful();

        $this->assertDatabaseHas('trucks', $updatedTruck);
    }

    public function test_truck_api_call_update_with_existing_code_in_different_truck_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingTruck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $newTruck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $updatedTruck = Truck::factory()
            ->for(Vendor::factory())
            ->make(['code' => $existingTruck->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/truck/' . $newTruck->id, $updatedTruck);

        $response->assertStatus(422);
    }

    public function test_truck_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/truck/active/' . $truck->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('trucks', ['id' => $truck->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/truck/active/' . $truck->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('trucks', ['id' => $truck->id, 'is_active' => true]);
    }

    public function test_truck_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('DELETE', '/api/v1/truck/' . $truck->id);

        $response->assertSuccessful();

        $truck = $truck->toArray();
        $truck = Arr::except($truck, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('trucks', $truck);
    }

    public function test_truck_api_call_check_availability_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/truck/check-availability/' . $truck->id);

        $response->assertSuccessful();
    }
}
