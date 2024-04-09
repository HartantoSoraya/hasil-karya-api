<?php

namespace App\Interfaces;

interface MaterialMovementRepositoryInterface
{
    public function getAllMaterialMovements();

    public function create(array $data);

    public function getMaterialMovementById(string $id);

    public function getMaterialMovementByTruck(string $truckId);

    // 1
    public function getStatisticTruckPerDayByStation($statisticType = null, $dateType = null, $stationCategory = null);

    // 2
    public function getStatisticRitagePerDayByStation($statisticType = null, $dateType = null, $stationCategory = null);

    // 3
    public function getStatisticRitageVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null);

    // 4
    public function getStatisticMeasurementVolumeByStation($statisticType = null, $dateType = null, $stationCategory = null);

    // 5
    public function getRatioMeasurementByRitage($statisticType = null, $dateType = null, $stationCategory = null);

    public function update(array $data, string $id);

    public function delete(string $id);

    public function generateCode(int $tryCount): string;

    public function isUniqueCode(string $code, $exceptId = null): bool;
}
