<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\GasOperator;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GasOperatorAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_gas_operator_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            GasOperator::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create();
        }

        $response = $this->json('GET', '/api/v1/gas-operators');

        $response->assertSuccessful();
    }

    public function test_gas_operator_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/gas-operator', $gasOperator);

        $response->assertSuccessful();

        $gasOperator['code'] = $response['data']['code'];

        $this->assertDatabaseHas('gas_operators', Arr::except($gasOperator, ['email', 'password']));

        $this->assertDatabaseHas('users', [
            'email' => $gasOperator['email'],
        ]);
    }

    public function test_gas_operator_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $response = $this->json('GET', '/api/v1/gas-operator/' . $gasOperator->id);

        $response->assertSuccessful();
    }

    public function test_gas_operator_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $updatedGasOperator = GasOperator::factory()
            ->withCredentials()
            ->make(['code' => 'AUTO'])
            ->toArray();

        $response = $this->json('POST', '/api/v1/gas-operator/' . $gasOperator->id, $updatedGasOperator);

        $response->assertSuccessful();

        $updatedGasOperator['code'] = $response['data']['code'];

        $this->assertDatabaseHas('gas_operators', Arr::except($updatedGasOperator, ['email', 'password']));
    }

    public function test_gas_operator_call_update_with_existing_code_in_same_gas_operator_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $updatedGasOperator = GasOperator::factory()
            ->withCredentials()
            ->make(['code' => $gasOperator->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/gas-operator/' . $gasOperator->id, $updatedGasOperator);

        $response->assertSuccessful();

        $this->assertDatabaseHas('gas_operators', Arr::except($updatedGasOperator, ['email', 'password']));
    }

    public function test_gas_operator_call_update_with_existing_code_in_different_gas_operator_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingGasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $newGasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $updatedGasOperator = GasOperator::factory()
            ->withCredentials()
            ->make(['code' => $existingGasOperator->code])
            ->toArray();

        $response = $this->json('POST', '/api/v1/gas-operator/' . $newGasOperator->id, $updatedGasOperator);

        $response->assertStatus(422);
    }

    public function test_gas_operator_call_update_active_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create(['is_active' => true]);

        $response = $this->json('POST', '/api/v1/gas-operator/active/' . $gasOperator->id, ['is_active' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('gas_operators', ['id' => $gasOperator->id, 'is_active' => false]);

        $response = $this->json('POST', '/api/v1/gas-operator/active/' . $gasOperator->id, ['is_active' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('gas_operators', ['id' => $gasOperator->id, 'is_active' => true]);
    }

    public function test_gas_operator_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $gasOperator = GasOperator::factory()
            ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
            ->create();

        $response = $this->json('DELETE', '/api/v1/gas-operator/' . $gasOperator->id);

        $response->assertSuccessful();

        $gasOperator = $gasOperator->toArray();
        $gasOperator = Arr::except($gasOperator, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('gas_operators', $gasOperator);
    }
}
