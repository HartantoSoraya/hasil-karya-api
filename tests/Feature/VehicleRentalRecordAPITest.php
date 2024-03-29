<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Truck;
use App\Models\User;
use App\Models\VehicleRentalRecord;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VehicleRentalRecordAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_vehicle_rental_record_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecords = [];
        for ($i = 0; $i < 5; $i++) {
            $vehicleRentalRecords[] = VehicleRentalRecord::factory()
                ->withExpectedCode()
                ->for($truck)
                ->create();
        }

        $response = $this->json('GET', '/api/v1/vehicle-rental-records');

        $response->assertSuccessful();

        foreach ($vehicleRentalRecords as $vehicleRentalRecord) {
            $vehicleRentalRecord = $vehicleRentalRecord->toArray();
            $vehicleRentalRecord = Arr::except($vehicleRentalRecord, ['created_at', 'updated_at', 'deleted_at']);
            $this->assertDatabaseHas('vehicle_rental_records', $vehicleRentalRecord);
        }
    }

    public function test_vehicle_rental_record_api_call_get_due_vehicle_rental_records()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $response = $this->json('GET', '/api/v1/vehicle-rental-records/read/due');

        $response->assertSuccessful();
    }

    public function test_vehicle_rental_record_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->make()->toArray();

        $response = $this->json('POST', '/api/v1/vehicle-rental-record', $vehicleRentalRecord);

        $response->assertSuccessful();

        $vehicleRentalRecord = Arr::except($vehicleRentalRecord, ['payment_proof_image', 'created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('vehicle_rental_records', $vehicleRentalRecord);
    }

    public function test_vehicle_rental_record_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $response = $this->json('GET', '/api/v1/vehicle-rental-record/' . $vehicleRentalRecord->id);

        $response->assertSuccessful();
    }

    public function test_vehicle_rental_record_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $updatedVehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->make()->toArray();

        $response = $this->json('POST', '/api/v1/vehicle-rental-record/' . $vehicleRentalRecord->id, $updatedVehicleRentalRecord);

        $response->assertSuccessful();

        $updatedVehicleRentalRecord = Arr::except($updatedVehicleRentalRecord, ['payment_proof_image', 'created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('vehicle_rental_records', $updatedVehicleRentalRecord);
    }

    public function test_vehicle_rental_record_api_call_update_with_existing_code_in_same_truck_rental_record_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $updatedVehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->make(['code' => $vehicleRentalRecord->code])->toArray();

        $response = $this->json('POST', '/api/v1/vehicle-rental-record/' . $vehicleRentalRecord->id, $updatedVehicleRentalRecord);

        $response->assertSuccessful();

        $updatedVehicleRentalRecord = Arr::except($updatedVehicleRentalRecord, ['payment_proof_image', 'created_at', 'updated_at', 'deleted_at']);

        $this->assertDatabaseHas('vehicle_rental_records', $updatedVehicleRentalRecord);
    }

    public function test_vehicle_rental_record_api_call_update_with_existing_code_in_different_truck_rental_record_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $existingVehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $newVehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $updatedVehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->make(['code' => $existingVehicleRentalRecord->code])->toArray();

        $response = $this->json('POST', '/api/v1/vehicle-rental-record/' . $newVehicleRentalRecord->id, $updatedVehicleRentalRecord);

        $response->assertStatus(422);
    }

    public function test_vehicle_rental_record_api_call_update_rental_payment_status_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create(['is_paid' => false]);

        $response = $this->json('POST', '/api/v1/vehicle-rental-record/payment/' . $vehicleRentalRecord->id, ['is_paid' => true]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vehicle_rental_records', ['id' => $vehicleRentalRecord->id, 'is_paid' => true]);

        $response = $this->json('POST', '/api/v1/vehicle-rental-record/payment/' . $vehicleRentalRecord->id, ['is_paid' => false]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('vehicle_rental_records', ['id' => $vehicleRentalRecord->id, 'is_paid' => false]);
    }

    public function test_vehicle_rental_record_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $truck = Truck::factory()
            ->for(Vendor::factory())
            ->create();

        $vehicleRentalRecord = VehicleRentalRecord::factory()
            ->for($truck)
            ->create();

        $response = $this->json('DELETE', '/api/v1/vehicle-rental-record/' . $vehicleRentalRecord->id);

        $response->assertSuccessful();

        $vehicleRentalRecord = Arr::except($vehicleRentalRecord->toArray(), ['created_at', 'updated_at', 'deleted_at']);

        $this->assertSoftDeleted('vehicle_rental_records', $vehicleRentalRecord);
    }
}
