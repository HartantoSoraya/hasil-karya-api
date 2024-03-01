<?php

namespace Tests\Feature;

use App\Models\Truck;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TruckAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_truck_api_call_index_expect_success()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Truck::factory()->count(5)->create();

        $response = $this->json('GET', '/api/v1/trucks');

        $response->assertSuccessful();
    }

    public function test_truck_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $truck = Truck::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/truck', $truck);

        $response->assertSuccessful();

        $this->assertDatabaseHas('trucks', $truck);
    }

    public function test_truck_api_call_show_expect_success()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $truck = Truck::factory()->create();

        $response = $this->json('GET', '/api/v1/truck/'.$truck->id);

        $response->assertSuccessful();
    }

    public function test_truck_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $truck = Truck::factory()->create();

        $updatedTruck = Truck::factory()->make()->toArray();

        $response = $this->json('POST', '/api/v1/truck/'.$truck->id, $updatedTruck);

        $response->assertSuccessful();

        $this->assertDatabaseHas('trucks', $updatedTruck);
    }

    public function test_truck_api_call_delete_expect_success()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $truck = Truck::factory()->create();

        $response = $this->json('DELETE', '/api/v1/truck/'.$truck->id);

        $response->assertSuccessful();

        $this->assertSoftDeleted('trucks', $truck->toArray());
    }
}
