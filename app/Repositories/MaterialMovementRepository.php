<?php

namespace App\Repositories;

use App\Enum\AggregateFunctionEnum;
use App\Enum\DatePeriodEnum;
use App\Interfaces\MaterialMovementRepositoryInterface;
use App\Models\MaterialMovement;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaterialMovementRepository implements MaterialMovementRepositoryInterface
{
    public function getAllMaterialMovements()
    {
        $materialMovements = MaterialMovement::with('driver', 'truck', 'station', 'checker')
            ->orderBy('date', 'desc')->get();

        return $materialMovements;
    }

    public function create(array $data)
    {
        $materialMovement = new MaterialMovement();
        $materialMovement->code = $data['code'];
        $materialMovement->driver_id = $data['driver_id'];
        $materialMovement->truck_id = $data['truck_id'];
        $materialMovement->station_id = $data['station_id'];
        $materialMovement->checker_id = $data['checker_id'];
        $materialMovement->date = $data['date'];
        $materialMovement->truck_capacity = $data['truck_capacity'];
        $materialMovement->observation_ratio_percentage = $data['observation_ratio_percentage'];
        $materialMovement->observation_ratio_number = $data['observation_ratio_percentage'] * $data['truck_capacity'];
        $materialMovement->solid_ratio = $data['solid_ratio'];
        $materialMovement->solid_volume_estimate = $materialMovement->observation_ratio_number * $data['solid_ratio'];
        $materialMovement->ratio_measurement_ritage = $materialMovement->solid_volume_estimate / $materialMovement->observation_ratio_number;
        $materialMovement->remarks = $data['remarks'];
        $materialMovement->save();

        return $materialMovement;
    }

    public function getMaterialMovementById($id)
    {
        $materialMovement = MaterialMovement::with('driver', 'truck', 'station', 'checker')
            ->find($id);

        return $materialMovement;
    }

    // 1
    public function getStatisticTruckPerDayByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.truck_id) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.truck_id) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.truck_id) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.truck_id) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.truck_id) as value';
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($dateType == DatePeriodEnum::TODAY->value) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($dateType == DatePeriodEnum::WEEK->value) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($dateType == DatePeriodEnum::MONTH->value) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($dateType == DatePeriodEnum::YEAR->value) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($dateType == DatePeriodEnum::ALL->value) {
            $startDate = MaterialMovement::orderBy('date', 'asc')->first()->date;
            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
        }

        $result = MaterialMovement::select('material_movements.station_id as station', DB::raw($rawQuery))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id', 'material_movements.truck_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : $item['value'];

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    // 2
    public function getStatisticRitagePerDayByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.observation_ratio_number) as value';
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($dateType == DatePeriodEnum::TODAY->value) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($dateType == DatePeriodEnum::WEEK->value) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($dateType == DatePeriodEnum::MONTH->value) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($dateType == DatePeriodEnum::YEAR->value) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($dateType == DatePeriodEnum::ALL->value) {
            $startDate = MaterialMovement::orderBy('date', 'asc')->first()->date;
            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
        }

        $result = MaterialMovement::select('material_movements.station_id as station', DB::raw($rawQuery))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : $item['value'];

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    // 3
    public function getStatisticMeasurementVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.observation_ratio_number) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.observation_ratio_number) as value';
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($dateType == DatePeriodEnum::TODAY->value) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($dateType == DatePeriodEnum::WEEK->value) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($dateType == DatePeriodEnum::MONTH->value) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($dateType == DatePeriodEnum::YEAR->value) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($dateType == DatePeriodEnum::ALL->value) {
            $startDate = MaterialMovement::orderBy('date', 'asc')->first()->date;
            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
        }

        $result = MaterialMovement::select('material_movements.station_id as station', DB::raw($rawQuery))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : $item['value'];

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    // 4
    public function getStatisticRitageVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.solid_volume_estimate) as value';
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($dateType == DatePeriodEnum::TODAY->value) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($dateType == DatePeriodEnum::WEEK->value) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($dateType == DatePeriodEnum::MONTH->value) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($dateType == DatePeriodEnum::YEAR->value) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($dateType == DatePeriodEnum::ALL->value) {
            $startDate = MaterialMovement::orderBy('date', 'asc')->first()->date;
            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
        }

        $result = MaterialMovement::select('material_movements.station_id as station', DB::raw($rawQuery))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : $item['value'];

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    // 5
    public function getRatioMeasurementByRitage($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        if ($dateType == DatePeriodEnum::TODAY->value) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($dateType == DatePeriodEnum::WEEK->value) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($dateType == DatePeriodEnum::MONTH->value) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($dateType == DatePeriodEnum::YEAR->value) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif ($dateType == DatePeriodEnum::ALL->value) {
            $startDate = MaterialMovement::orderBy('date', 'asc')->first()->date;
            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
        }

        $observationRatio = MaterialMovement::select('material_movements.station_id as station', DB::raw('SUM(material_movements.observation_ratio_number) as value'))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $solidVolumeEstimate = MaterialMovement::select('material_movements.station_id as station', DB::raw('SUM(material_movements.solid_volume_estimate) as value'))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $observationRatio->map(function ($item) use ($solidVolumeEstimate) {
            $solidVolumeEstimateItem = $solidVolumeEstimate->where('station', $item['station'])->first();
            $item['value'] = $solidVolumeEstimateItem['value'] / $item['value'];

            return $item;
        });

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : $item['value'];

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    public function update(array $data, $id)
    {
        $materialMovement = MaterialMovement::find($id);
        $materialMovement->code = $data['code'];
        $materialMovement->driver_id = $data['driver_id'];
        $materialMovement->truck_id = $data['truck_id'];
        $materialMovement->station_id = $data['station_id'];
        $materialMovement->checker_id = $data['checker_id'];
        $materialMovement->date = $data['date'];
        $materialMovement->truck_capacity = $data['truck_capacity'];
        $materialMovement->observation_ratio_percentage = $data['observation_ratio_percentage'];
        $materialMovement->observation_ratio_number = $data['observation_ratio_percentage'] * $data['truck_capacity'];
        $materialMovement->solid_ratio = $data['solid_ratio'];
        $materialMovement->solid_volume_estimate = $materialMovement->observation_ratio_number * $data['solid_ratio'];
        $materialMovement->ratio_measurement_ritage = $materialMovement->solid_volume_estimate / $materialMovement->observation_ratio_number;
        $materialMovement->remarks = $data['remarks'];
        $materialMovement->save();

        return $materialMovement;
    }

    public function delete($id)
    {
        $materialMovement = MaterialMovement::find($id);
        $materialMovement->delete();

        return $materialMovement;
    }

    public function generateCode(int $tryCount): string
    {
        $count = MaterialMovement::withTrashed()->count() + 1 + $tryCount;
        $code = 'MM'.str_pad($count, 2, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = MaterialMovement::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }
}
