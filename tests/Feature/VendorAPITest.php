<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_vendor_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Vendor::factory()->withExpectedCode()->create();
        }

        $response = $this->json('GET', '/api/v1/vendors');

        $response->assertSuccessful();
    }

    public function test_vendor_api_call_create_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/vendor', $vendor);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vendors', $vendor);
    }

    public function test_vendor_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->create();

        $response = $this->json('GET', '/api/v1/vendor/'.$vendor->id);

        $response->assertSuccessful();
    }

    public function test_vendor_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->create();

        $updatedVendor = Vendor::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/vendor/'.$vendor->id, $updatedVendor);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vendors', $updatedVendor);
    }

    public function test_vendor_api_call_update_with_existing_code_in_same_truck_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->create();

        $updatedVendor = Vendor::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/vendor/'.$vendor->id, $updatedVendor);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vendors', $updatedVendor);
    }

    public function test_vendor_api_call_update_with_existing_code_in_different_vendor_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingVendor = Vendor::factory()->create();

        $newVendor = Vendor::factory()->create();

        $updatedVendor = Vendor::factory()->make(['code' => $existingVendor->code])->toArray();

        $response = $this->json('POST', '/api/v1/vendor/'.$newVendor->id, $updatedVendor);

        $response->assertStatus(422);
    }

    public function test_vendor_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/vendor/active/'.$vendor->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/vendor/active/'.$vendor->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vendors', ['id' => $vendor->id, 'is_active' => true]);
    }

    public function test_vendor_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $vendor = Vendor::factory()->create();

        $response = $this->json('DELETE', '/api/v1/vendor/'.$vendor->id);

        $response->assertSuccessful();

        $vendor = $vendor->toArray();
        $vendor = Arr::except($vendor, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('vendors', $vendor);
    }
}
