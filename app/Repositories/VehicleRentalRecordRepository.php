<?php

namespace App\Repositories;

use App\Interfaces\VehicleRentalRecordRepositoryInterface;
use App\Models\VehicleRentalRecord;
use Illuminate\Support\Facades\Storage;

class VehicleRentalRecordRepository implements VehicleRentalRecordRepositoryInterface
{
    public function getAllVehicleRentalRecords()
    {
        $vehicleRentalRecords = VehicleRentalRecord::with('truck', 'heavyVehicle')
            ->orderBy('start_date', 'desc')
            ->get();

        return $vehicleRentalRecords;
    }

    public function getDueVehicleRentalRecords()
    {
        // Get all vehicle rental records that are not paid and due for payment is start date + rental_duration
        $vehicleRentalRecords = VehicleRentalRecord::with('truck', 'heavyVehicle')
            ->where('is_paid', false)
            ->whereRaw('DATE_ADD(start_date, INTERVAL rental_duration DAY) <= CURDATE()')
            ->orderBy('start_date', 'desc')
            ->get();

        // $vehicleRentalRecords = VehicleRentalRecord::with('truck', 'heavyVehicle')
        // ->where('start_date', '<=', now()->subDays(1))
        // ->where('is_paid', false)
        // ->get();

        return $vehicleRentalRecords;

    }

    public function create(array $data)
    {
        $vehicleRentalRecord = new VehicleRentalRecord();
        $vehicleRentalRecord->code = $data['code'];
        $vehicleRentalRecord->truck_id = $data['truck_id'];
        $vehicleRentalRecord->heavy_vehicle_id = $data['heavy_vehicle_id'];
        $vehicleRentalRecord->start_date = $data['start_date'];
        $vehicleRentalRecord->rental_duration = $data['rental_duration'];
        $vehicleRentalRecord->rental_cost = $data['rental_cost'];
        $vehicleRentalRecord->is_paid = $data['is_paid'];
        $vehicleRentalRecord->remarks = $data['remarks'];
        if ($data['payment_proof_image']) {
            $vehicleRentalRecord->payment_proof_image = $data['payment_proof_image']->store('assets/vehicle_rental_records/payment_proofs', 'public');
        }
        $vehicleRentalRecord->save();

        return $vehicleRentalRecord;
    }

    public function getVehicleRentalRecordById(string $id)
    {
        $vehicleRentalRecord = VehicleRentalRecord::with('truck', 'heavyVehicle')
            ->find($id);

        return $vehicleRentalRecord;
    }

    public function update(array $data, string $id)
    {
        $vehicleRentalRecord = VehicleRentalRecord::find($id);
        $vehicleRentalRecord->code = $data['code'];
        $vehicleRentalRecord->truck_id = $data['truck_id'];
        $vehicleRentalRecord->heavy_vehicle_id = $data['heavy_vehicle_id'];
        $vehicleRentalRecord->start_date = $data['start_date'];
        $vehicleRentalRecord->rental_duration = $data['rental_duration'];
        $vehicleRentalRecord->rental_cost = $data['rental_cost'];
        $vehicleRentalRecord->is_paid = $data['is_paid'];
        $vehicleRentalRecord->remarks = $data['remarks'];
        if ($data['payment_proof_image']) {
            $vehicleRentalRecord->payment_proof_image = $this->updateImage($vehicleRentalRecord->payment_proof_image, $data['payment_proof_image']);
        }
        $vehicleRentalRecord->save();

        return $vehicleRentalRecord;
    }

    public function updateRentalPaymentStatus(string $id, bool $status)
    {
        $vehicleRentalRecord = VehicleRentalRecord::find($id);
        $vehicleRentalRecord->is_paid = $status;
        $vehicleRentalRecord->save();

        return $vehicleRentalRecord;
    }

    public function delete(string $id)
    {
        $vehicleRentalRecord = VehicleRentalRecord::find($id);
        $vehicleRentalRecord->delete();

        return $vehicleRentalRecord;
    }

    public function generateCode(int $tryCount): string
    {
        $count = VehicleRentalRecord::withTrashed()->count() + 1 + $tryCount;
        $code = 'VRR'.str_pad($count, 3, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = VehicleRentalRecord::where('code', $code);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->count() == 0;
    }

    private function updateImage($oldImage, $newImage)
    {
        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $newImage->store('assets/vehicle_rental_records/payment_proofs', 'public');
    }
}
