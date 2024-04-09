<?php

namespace App\Repositories;

use App\Enum\AggregateFunctionEnum;
use App\Enum\DatePeriodEnum;
use App\Interfaces\MaterialMovementRepositoryInterface;
use App\Models\MaterialMovement;
use App\Models\MaterialMovementSolidVolumeEstimate;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaterialMovementRepository implements MaterialMovementRepositoryInterface
{
    public function getAllMaterialMovements()
    {
        $materialMovements = MaterialMovement::with('driver', 'truck', 'station', 'checker')
            ->orderBy('date', 'desc')->get();

        $materialMovements = $this->processMaterialMovements($materialMovements);

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
        $materialMovement->observation_ratio = $data['observation_ratio'];
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

    public function getMaterialMovementByTruck(string $truckId)
    {
        try {
            $materialMovements = MaterialMovement::with('driver', 'truck', 'station', 'checker')
                ->where('truck_id', $truckId)
                ->orderBy('date', 'asc')->get();

            $previousDate = Carbon::parse('1900-01-01');
            $materialMovements = $materialMovements->map(function ($item, $key) use (&$previousDate) {
                $date = Carbon::parse($item['date']);

                if ($key == 0) {
                    $previousDate = $date;
                    $item['date_difference'] = '';
                } else {
                    $currentDate = $date;
                    $differenceString = '';

                    if ($currentDate->isSameDay($previousDate)) {
                        $difference = $currentDate->diff($previousDate);
                        $differenceString = $difference->format('%h jam %i menit %s detik');
                    }

                    $item['date_difference'] = $differenceString;
                    $previousDate = $currentDate;
                }

                return $item;
            });

            $materialMovements = $materialMovements->sortByDesc('date')->values();

            $materialMovements = $this->processMaterialMovements($materialMovements);

            return $materialMovements;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // 1
    public function getStatisticTruckPerDayByStation($statisticType = null, $dateType = null, $stationCategory = null)
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
            $startDate = Carbon::parse($startDate)->startOfDay();

            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $data = MaterialMovement::select('material_movements.station_id as station', DB::raw('COUNT(material_movements.truck_id) as value'))
            ->leftJoin('stations', 'stations.id', '=', 'material_movements.station_id')
            ->whereBetween(DB::raw('DATE(material_movements.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movements.station_id', DB::raw('DATE(material_movements.date)'))
            ->orderBy('stations.name', 'ASC')
            ->get();

        $station_ids = $data->pluck('station')->unique();
        $stations = Station::whereIn('id', $station_ids)->get()->toArray();

        $result = [];
        foreach ($stations as $station) {
            $stationData = $data->where('station', $station['id']);
            $value = 0;
            if ($statisticType == AggregateFunctionEnum::MIN->value) {
                $value = $stationData->min('value');
            } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
                $value = $stationData->max('value');
            } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
                $value = $stationData->avg('value');
            } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
                $value = $stationData->sum('value');
            } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
                $value = $stationData->count();
            }

            $result[] = [
                'station' => $station['name'],
                'value' => $value,
            ];
        }

        $result = response()->json($result);

        return $result;
    }

    // 2
    public function getStatisticRitagePerDayByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.observation_ratio) as value';
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
            $startDate = Carbon::parse($startDate)->startOfDay();

            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
            $endDate = Carbon::parse($endDate)->endOfDay();
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
    public function getStatisticRitageVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movements.observation_ratio) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movements.observation_ratio) as value';
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
            $startDate = Carbon::parse($startDate)->startOfDay();

            $endDate = MaterialMovement::orderBy('date', 'desc')->first()->date;
            $endDate = Carbon::parse($endDate)->endOfDay();
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
    public function getStatisticMeasurementVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $rawQuery = '';
        if ($statisticType == AggregateFunctionEnum::MIN->value) {
            $rawQuery = 'MIN(material_movement_solid_volume_estimates.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::MAX->value) {
            $rawQuery = 'MAX(material_movement_solid_volume_estimates.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::AVG->value) {
            $rawQuery = 'AVG(material_movement_solid_volume_estimates.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::SUM->value) {
            $rawQuery = 'SUM(material_movement_solid_volume_estimates.solid_volume_estimate) as value';
        } elseif ($statisticType == AggregateFunctionEnum::COUNT->value) {
            $rawQuery = 'COUNT(material_movement_solid_volume_estimates.solid_volume_estimate) as value';
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
            $startDate = MaterialMovementSolidVolumeEstimate::orderBy('date', 'asc')->first()->date;
            $startDate = Carbon::parse($startDate)->startOfDay();

            $endDate = MaterialMovementSolidVolumeEstimate::orderBy('date', 'desc')->first()->date;
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        $result = MaterialMovementSolidVolumeEstimate::select('material_movement_solid_volume_estimates.station_id as station', DB::raw($rawQuery))
            ->leftJoin('stations', 'stations.id', '=', 'material_movement_solid_volume_estimates.station_id')
            ->whereBetween(DB::raw('DATE(material_movement_solid_volume_estimates.date)'), [$startDate, $endDate])
            ->where('stations.category', $stationCategory)
            ->groupBy('material_movement_solid_volume_estimates.station_id')
            ->orderBy('stations.name', 'ASC')
            ->get();

        $result = $result->map(function ($item) {
            $item['station'] = Station::find($item['station'])->name;
            $item['value'] = is_numeric($item['value']) ? $item['value'] * 1 : 0;

            return $item;
        });

        $result = response()->json($result);

        return $result;
    }

    // 5
    public function getRatioMeasurementByRitage($statisticType = null, $dateType = null, $stationCategory = null)
    {
        $observationRatios = $this->getStatisticRitageVolumeByStation(AggregateFunctionEnum::SUM->value, $dateType, $stationCategory);
        $observationRatios = json_decode($observationRatios->content(), true);
        $observationRatios = collect($observationRatios);

        $solidVolumeEstimates = $this->getStatisticMeasurementVolumeByStation(AggregateFunctionEnum::SUM->value, $dateType, $stationCategory);
        $solidVolumeEstimates = json_decode($solidVolumeEstimates->content(), true);
        $solidVolumeEstimates = collect($solidVolumeEstimates);

        $result = $observationRatios->map(function ($item) use ($solidVolumeEstimates) {
            $solidVolumeEstimateItem = $solidVolumeEstimates->where('station', $item['station'])->first();
            if ($solidVolumeEstimateItem) {
                $item['value'] = $solidVolumeEstimateItem['value'] / $item['value'];
            } else {
                $item['value'] = 0;
            }

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
        $materialMovement->observation_ratio = $data['observation_ratio'];
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

    private function processMaterialMovements($SourceMaterialMovements)
    {
        $materialMovements = $SourceMaterialMovements;

        $lastDate = MaterialMovementSolidVolumeEstimate::orderBy('date', 'desc')->first();

        if ($lastDate) {
            $lastDate = $lastDate->date;
        } else {
            $lastDate = Carbon::now();
        }

        $solidVolumeEstimateTotal = MaterialMovementSolidVolumeEstimate::select('station_id', DB::raw('SUM(solid_volume_estimate) as value'))
            ->where('date', '<=', $lastDate)
            ->groupBy('station_id')
            ->get();

        $observationRatioTotal = MaterialMovement::select('station_id', DB::raw('SUM(observation_ratio) as value'))
            ->where('date', '<=', $lastDate)
            ->groupBy('station_id')
            ->get();

        $materialMovements = $materialMovements->map(function ($item) use ($solidVolumeEstimateTotal, $observationRatioTotal) {
            $solidVolumeEstimateTotalItem = $solidVolumeEstimateTotal->where('station_id', $item['station_id'])->first();
            $observationRatioTotalItem = $observationRatioTotal->where('station_id', $item['station_id'])->first();

            $item['solid_ratio'] = ($solidVolumeEstimateTotalItem['value'] ?? 0) / ($observationRatioTotalItem['value'] + 0);
            $item['solid_volume_estimate'] = $item['observation_ratio'] * $item['solid_ratio'];

            return $item;
        });

        return $materialMovements;
    }
}
