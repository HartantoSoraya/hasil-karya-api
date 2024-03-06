<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Driver;
use App\Models\User;
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

        Driver::factory()->count(5)->create();

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

    public function test_driver_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $driver = Driver::factory()->create();

        $response = $this->json('DELETE', '/api/v1/driver/'.$driver->id);

        $response->assertSuccessful();

        $this->assertSoftDeleted('drivers', $driver->toArray());
    }
}
