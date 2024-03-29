<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DriverAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_driver_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Driver::factory()->withExpectedCode()->create();
        }

        $response = $this->json('GET', '/api/v1/drivers');

        $response->assertSuccessful();
    }

    public function test_driver_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/driver', $driver);

        $response->assertSuccessful();

        $this->assertDatabaseHas('drivers', $driver);
    }

    public function test_driver_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create();

        $response = $this->json('GET', '/api/v1/driver/'.$driver->id);

        $response->assertSuccessful();
    }

    public function test_driver_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create();

        $updatedDriver = Driver::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/driver/'.$driver->id, $updatedDriver);

        $response->assertSuccessful();

        $this->assertDatabaseHas('drivers', $updatedDriver);
    }

    public function test_driver_api_call_update_with_existing_code_in_same_driver_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create();

        $updatedDriver = Driver::factory()->make(['code' => $driver->code])->toArray();

        $response = $this->json('POST', '/api/v1/driver/'.$driver->id, $updatedDriver);

        $response->assertSuccessful();

        $this->assertDatabaseHas('drivers', $updatedDriver);
    }

    public function test_driver_api_call_update_with_existing_code_in_different_driver_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingDriver = Driver::factory()->create();

        $newDriver = Driver::factory()->create();

        $updatedDriver = Driver::factory()->make(['code' => $existingDriver->code])->toArray();

        $response = $this->json('POST', '/api/v1/driver/'.$newDriver->id, $updatedDriver);

        $response->assertStatus(422);
    }

    public function test_driver_api_call_update_active_status_expect_succes()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/driver/active/'.$driver->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/driver/active/'.$driver->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'is_active' => true]);
    }

    public function test_driver_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create();

        $response = $this->json('DELETE', '/api/v1/driver/'.$driver->id);

        $response->assertSuccessful();

        $driver = $driver->toArray();
        $driver = Arr::except($driver, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('drivers', $driver);
    }
}
