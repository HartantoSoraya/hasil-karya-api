<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\TechnicalAdmin;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TechnicalAdminAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_technical_admin_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            TechnicalAdmin::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
                ->create();
        }

        $response = $this->json('GET', '/api/v1/technical-admins');

        $response->assertSuccessful();
    }

    public function test_technical_admin_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/technical-admin', $technicalAdmin);

        $response->assertSuccessful();

        $technicalAdmin['code'] = $response['data']['code'];

        $this->assertDatabaseHas('technical_admins', Arr::except($technicalAdmin, ['email', 'password']));

        $this->assertDatabaseHas('users', [
            'email' => $technicalAdmin['email'],
        ]);
    }

    public function test_technicel_admin_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $response = $this->json('GET', '/api/v1/technical-admin/' . $technicalAdmin->id);

        $response->assertSuccessful();
    }

    public function test_technical_admin_api_call_update_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $updatedTechnicalAdmin = TechnicalAdmin::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/technical-admin/' . $technicalAdmin->id, $updatedTechnicalAdmin);

        $response->assertSuccessful();

        $updatedTechnicalAdmin['code'] = $response['data']['code'];

        $this->assertDatabaseHas('technical_admins', Arr::except($updatedTechnicalAdmin, ['email', 'password']));
    }

    public function test_technical_admin_api_call_update_with_existing_code_in_same_checker_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $updatedTechnicalAdmin = TechnicalAdmin::factory()
            ->withCredentials()
            ->make(['code' => $technicalAdmin->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/technical-admin/' . $technicalAdmin->id, $updatedTechnicalAdmin);

        $response->assertSuccessful();

        $this->assertDatabaseHas('technical_admins', Arr::except($updatedTechnicalAdmin, ['email', 'password']));
    }

    public function test_technical_admin_api_call_update_with_existing_code_in_another_checker_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingTechnicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $newTechnicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $updatedTechnicalAdmin = TechnicalAdmin::factory()
            ->withCredentials()
            ->make(['code' => $existingTechnicalAdmin->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/technical-admin/' . $newTechnicalAdmin->id, $updatedTechnicalAdmin);

        $response->assertStatus(422);
    }

    public function test_technical_admin_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/technical-admin/active/' . $technicalAdmin->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('technical_admins', ['id' => $technicalAdmin->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/technical-admin/active/' . $technicalAdmin->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('technical_admins', ['id' => $technicalAdmin->id, 'is_active' => true]);
    }

    public function test_technical_admin_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $technicalAdmin = TechnicalAdmin::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
            ->create();

        $response = $this->json('DELETE', '/api/v1/technical-admin/' . $technicalAdmin->id);

        $response->assertSuccessful();

        $technicalAdmin = $technicalAdmin->toArray();
        $technicalAdmin = Arr::except($technicalAdmin, ['deleted_at', 'created_at', 'updated_at', 'user']);

        $this->assertSoftDeleted('technical_admins', $technicalAdmin);
    }
}
