<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\MaterialMovementSolidVolumeEstimate;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialMovementSolidVolumeEstimateAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_material_movement_solid_volume_estimate_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        MaterialMovementSolidVolumeEstimate::factory()
            ->for(Station::factory())
            ->count(5)->create();

        $response = $this->json('GET', '/api/v1/material-movement-solid-volume-estimates');

        $response->assertSuccessful();
    }

    public function test_material_movement_solid_volume_estimate_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()->create(
            ['is_active' => true]
        );

        $materialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
            ->for($station)
            ->make([
                'code' => 'AUTO',
            ])->toArray();

        $response = $this->json('POST', '/api/v1/material-movement-solid-volume-estimate', $materialMovementSolidVolumeEstimate);

        $response->assertSuccessful();
    }

    public function test_material_movement_solid_volume_estimate_api_call_create_with_auto_code_with_all_existing_station_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $stations = Station::where('is_active', true)->get();

        if (! $stations) {
            $this->markTestSkipped('No active station found');
        }

        foreach ($stations as $station) {
            $materialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
                ->for($station)
                ->make([
                    'code' => 'AUTO',
                ])->toArray();

            $response = $this->json('POST', '/api/v1/material-movement-solid-volume-estimate', $materialMovementSolidVolumeEstimate);

            $response->assertSuccessful();
        }

        $response = $this->json('POST', '/api/v1/material-movement-solid-volume-estimate', $materialMovementSolidVolumeEstimate);

        $response->assertSuccessful();
    }

    public function test_material_movement_solid_volume_estimate_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $materialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
            ->for(Station::factory())
            ->create();

        $response = $this->json('GET', '/api/v1/material-movement-solid-volume-estimate/'.$materialMovementSolidVolumeEstimate->id);

        $response->assertSuccessful();
    }

    public function test_material_movement_solid_volume_estimate_api_call_update_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $station = Station::factory()->create(
            ['is_active' => true]
        );

        $existingMaterialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
            ->for($station)->create();

        $materialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
            ->for($station)->make()->toArray();

        $response = $this->json('POST', '/api/v1/material-movement-solid-volume-estimate/'.$existingMaterialMovementSolidVolumeEstimate->id, $materialMovementSolidVolumeEstimate);

        $response->assertSuccessful();
    }

    public function test_material_movement_solid_volume_estimate_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $materialMovementSolidVolumeEstimate = MaterialMovementSolidVolumeEstimate::factory()
            ->for(Station::factory())
            ->create();

        $response = $this->json('DELETE', '/api/v1/material-movement-solid-volume-estimate/'.$materialMovementSolidVolumeEstimate->id);

        $response->assertSuccessful();
    }
}
