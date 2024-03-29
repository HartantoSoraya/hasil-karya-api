<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Material;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StationAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_station_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Station::factory()->withExpectedCode()->create();
        }

        $response = $this->json('GET', '/api/v1/stations');

        $response->assertSuccessful();
    }

    public function test_station_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/station', $station);

        $response->assertSuccessful();

        $station['code'] = $response['data']['code'];

        $this->assertDatabaseHas('stations', $station);
    }

    public function test_station_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/station/'.$station->id);

        $response->assertSuccessful();
    }

    public function test_station_api_call_read_categories_expect_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $response = $this->json('GET', '/api/v1/station/read/categories');

        $response->assertSuccessful();
    }

    public function test_station_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->create();

        $updatedStation = Station::factory()
            ->for(Material::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/station/'.$station->id, $updatedStation);

        $response->assertSuccessful();

        $updatedStation['code'] = $response['data']['code'];

        $this->assertDatabaseHas('stations', $updatedStation);
    }

    public function test_station_api_call_update_with_existing_code_in_same_station_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->create();

        $updatedStation = Station::factory()
            ->for(Material::factory())
            ->make(['code' => $station->code])->toArray();

        $response = $this->json('POST', '/api/v1/station/'.$station->id, $updatedStation);

        $response->assertSuccessful();

        $this->assertDatabaseHas('stations', $updatedStation);
    }

    public function test_station_api_call_update_with_existing_code_in_different_station_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingStation = Station::factory()
            ->for(Material::factory())
            ->create();

        $newStation = Station::factory()
            ->for(Material::factory())
            ->create();

        $updatedStation = Station::factory()
            ->for(Material::factory())
            ->make(['code' => $existingStation->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/station/'.$newStation->id, $updatedStation);

        $response->assertStatus(422);
    }

    public function test_station_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/station/active/'.$station->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('stations', ['id' => $station->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/station/active/'.$station->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('stations', ['id' => $station->id, 'is_active' => false]);
    }

    public function test_station_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()
            ->for(Material::factory())
            ->create();

        $response = $this->json('DELETE', '/api/v1/station/'.$station->id);

        $response->assertSuccessful();

        $station = $station->toArray();
        $station = Arr::except($station, ['deleted_at', 'created_at', 'updated_at']);

        $this->assertSoftDeleted('stations', $station);
    }
}
