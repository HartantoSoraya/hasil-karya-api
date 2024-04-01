<?php

namespace App\Repositories;

use App\Interfaces\MaterialMovementErrorLogRepositoryInterface;
use App\Models\MaterialMovementErrorLog;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class MaterialMovementErrorLogRepository implements MaterialMovementErrorLogRepositoryInterface
{
    public function getAllMaterialMovementErrorLogs()
    {
        $materialMovementErrorLogs = MaterialMovementErrorLog::orderBy('created_at', 'desc')->get();

        foreach ($materialMovementErrorLogs as $idx => $materialMovementErrorLog) {
            $activityLog = Activity::where('subject_id', $materialMovementErrorLog->id)
                ->where('subject_type', MaterialMovementErrorLog::class)->first();

            if ($activityLog) {
                $causer = User::find($activityLog->causer_id);

                if ($causer->hasChecker()) {
                    $materialMovementErrorLogs[$idx]['creator_type'] = 'Pemeriksa Perpindahan Material';
                    $materialMovementErrorLogs[$idx]['created_by'] = $causer->checker->name;
                } elseif ($causer->hasgasOperator()) {
                    $materialMovementErrorLogs[$idx]['creator_type'] = 'Solar Man';
                    $materialMovementErrorLogs[$idx]['created_by'] = $causer->gasOperator->name;
                } elseif ($causer->hasTechnicalAdmin()) {
                    $materialMovementErrorLogs[$idx]['creator_type'] = 'Admin Teknik';
                    $materialMovementErrorLogs[$idx]['created_by'] = $causer->technicalAdmin->name;
                } else {
                    $materialMovementErrorLogs[$idx]['creator_type'] = 'Pengguna Lain';
                    $materialMovementErrorLogs[$idx]['created_by'] = $causer->email;
                }
            } else {
                $materialMovementErrorLogs[$idx]['creator_type'] = '';
                $materialMovementErrorLogs[$idx]['created_by'] = '';
            }
        }

        return $materialMovementErrorLogs;
    }

    public function create(array $data)
    {
        $materialMovementErrorLog = new MaterialMovementErrorLog();
        $materialMovementErrorLog->code = $data['code'];
        $materialMovementErrorLog->driver_id = $data['driver_id'];
        $materialMovementErrorLog->truck_id = $data['truck_id'];
        $materialMovementErrorLog->station_id = $data['station_id'];
        $materialMovementErrorLog->checker_id = $data['checker_id'];
        $materialMovementErrorLog->date = $data['date'];
        $materialMovementErrorLog->truck_capacity = $data['truck_capacity'];
        $materialMovementErrorLog->observation_ratio = $data['observation_ratio'];
        $materialMovementErrorLog->solid_ratio = $data['solid_ratio'];
        $materialMovementErrorLog->remarks = $data['remarks'];
        $materialMovementErrorLog->error_log = $data['error_log'];
        $materialMovementErrorLog->save();

        return $materialMovementErrorLog;
    }

    public function getMaterialMovementErrorLogById(string $id)
    {
        $materialMovementErrorLog = MaterialMovementErrorLog::find($id);

        return $materialMovementErrorLog;
    }
}
