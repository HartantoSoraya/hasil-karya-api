<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_client_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);
        
        $clients = [];
        for ($i = 0; $i < 5; $i++) {
            $clients[] = Client::factory()->withExpectedCode()->create();
        }

        $response = $this->json('GET', '/api/v1/clients');

        $response->assertSuccessful();

        foreach ($clients as $client) {
            $client = $client->toArray();
            $client = Arr::except($client, ['created_at', 'updated_at', 'deleted_at']);

            $this->assertDatabaseHas('clients', $client);
        }
    }

    public function test_client_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $client = Client::factory()->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/client', $client);

        $response->assertSuccessful();

        $client['code'] = $response['data']['code'];

        $this->assertDatabaseHas('clients', $client);
    }

    public function test_client_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $client = Client::factory()->create();

        $response = $this->json('GET', '/api/v1/client/'.$client->id);

        $response->assertSuccessful();

        $client = $client->toArray();
        $client = Arr::except($client, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('clients', $client);
    }

    public function test_client_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $client = Client::factory()->create();

        $updatedClient = Client::factory()->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/client/'.$client['id'], $updatedClient);

        $response->assertSuccessful();

        $updatedClient['code'] = $response['data']['code'];

        $this->assertDatabaseHas('clients', $updatedClient);
    }

    public function test_client_api_call_update_with_existing_code_in_same_client_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $client = Client::factory()->create();

        $updatedClient = Client::factory()->make(['code' => $client->code])->toArray();

        $response = $this->json('POST', '/api/v1/client/'.$client->id, $updatedClient);

        $response->assertSuccessful();

        $this->assertDatabaseHas('clients', $updatedClient);
    }

    public function test_client_api_call_update_with_existing_code_in_different_client_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingClient = Client::factory()->create();

        $newClient = Client::factory()->create();

        $updatedClient = Client::factory()->make(['code' => $existingClient->code])->toArray();

        $response = $this->json('POST', '/api/v1/client/'.$newClient->id, $updatedClient);

        $response->assertStatus(422);
    }

    public function test_client_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $client = Client::factory()->create();

        $response = $this->json('DELETE', '/api/v1/client/'.$client->id);

        $response->assertSuccessful();

        $client = $client->toArray();
        $client = Arr::except($client, ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('clients', $client);
    }
}
