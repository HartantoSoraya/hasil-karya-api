<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Checker;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckerAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_checker_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Checker::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
                ->create();
        }

        $response = $this->json('GET', '/api/v1/checkers');

        $response->assertSuccessful();
    }

    public function test_checker_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/checker', $checker);

        $response->assertSuccessful();

        $checker['code'] = $response['data']['code'];

        $this->assertDatabaseHas('checkers', Arr::except($checker, ['email', 'password']));

        $this->assertDatabaseHas('users', [
            'email' => $checker['email'],
        ]);
    }

    public function test_checker_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $response = $this->json('GET', '/api/v1/checker/' . $checker->id);

        $response->assertSuccessful();
    }

    public function test_checker_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $updatedChecker = Checker::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/checker/' . $checker->id, $updatedChecker);

        $response->assertSuccessful();

        $updatedChecker['code'] = $response['data']['code'];

        $this->assertDatabaseHas('checkers', Arr::except($updatedChecker, ['email', 'password']));
    }

    public function test_checker_api_call_update_with_existing_code_in_same_checker_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $updatedChecker = Checker::factory()
            ->withCredentials()
            ->make(['code' => $checker->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/checker/' . $checker->id, $updatedChecker);

        $response->assertSuccessful();

        $this->assertDatabaseHas('checkers', Arr::except($updatedChecker, ['email', 'password']));
    }

    public function test_checker_api_call_update_with_existing_code_in_different_checker_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingChecker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $newChecker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $updatedChecker = Checker::factory()
            ->withCredentials()
            ->make(['code' => $existingChecker->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/checker/' . $newChecker->id, $updatedChecker);

        $response->assertStatus(422);
    }

    public function test_checker_api_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/checker/active/' . $checker->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('checkers', ['id' => $checker->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/checker/active/' . $checker->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('checkers', ['id' => $checker->id, 'is_active' => true]);
    }

    public function test_checker_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $checker = Checker::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
            ->create();

        $response = $this->json('DELETE', '/api/v1/checker/' . $checker->id);

        $response->assertSuccessful();

        $checker = $checker->toArray();
        $checker = Arr::except($checker, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('checkers', $checker);
    }
}
