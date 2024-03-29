<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaterialAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_material_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $materials =  [];
        for ($i = 0; $i < 5; $i++) {
            $materials[] = Material::factory()->withExpectedCode()->create();
        }

        $response = $this->json('GET', '/api/v1/materials');

        $response->assertSuccessful();

        foreach ($materials as $material) {
            $material = $material->toArray();
            $material = Arr::except($material, ['created_at', 'updated_at', 'deleted_at']);
            $this->assertDatabaseHas('materials', $material);
        }
    }

    public function test_material_api_call_create_with_auto_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $material = Material::factory()
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/material', $material);

        $response->assertSuccessful();

        $material['code'] = $response['data']['code'];

        $this->assertDatabaseHas('materials', $material);
    }

    public function test_material_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $material = Material::factory()
            ->create();

        $response = $this->json('GET', '/api/v1/material/' . $material->id);

        $response->assertSuccessful();
    }

    public function test_material_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $material = Material::factory()
            ->create();

        $updatedMaterial = Material::factory()
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/material/' . $material->id, $updatedMaterial);

        $response->assertSuccessful();

        $updatedMaterial['code'] = $response['data']['code'];

        $updatedMaterial = Arr::except($updatedMaterial, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('materials', $updatedMaterial);
    }

    public function test_material_api_call_update_with_existing_code_in_same_material_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $material = Material::factory()
            ->create();

        $updatedMaterial = Material::factory()
            ->make(['code' => $material->code])->toArray();

        $response = $this->json('POST', '/api/v1/material/' . $material->id, $updatedMaterial);

        $response->assertSuccessful();

        $updatedMaterial['code'] = $response['data']['code'];

        $this->assertDatabaseHas('materials', $updatedMaterial);
    }

    public function test_material_api_call_update_with_existing_code_in_different_material_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingMaterial = Material::factory()
            ->create();

        $newMaterial = Material::factory()
            ->create();

        $updatedMaterial = Material::factory()->make(['code' => $existingMaterial->code])->toArray();

        $response = $this->json('POST', '/api/v1/material/' . $newMaterial->id, $updatedMaterial);

        $response->assertStatus(422);
    }

    public function test_material_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $material = Material::factory()
            ->create();

        $response = $this->json('DELETE', '/api/v1/material/' . $material->id);

        $response->assertSuccessful();

        $material = $material->toArray();
        $material = Arr::except($material, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('materials', $material);
    }
}
