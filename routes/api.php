<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckerController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\FuelLogController;
use App\Http\Controllers\Api\FuelLogErrorLogController;
use App\Http\Controllers\Api\GasOperatorController;
use App\Http\Controllers\Api\HeavyVehicleController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\MaterialMovementController;
use App\Http\Controllers\Api\MaterialMovementErrorLogController;
use App\Http\Controllers\Api\MaterialMovementSolidVolumeEstimateController;
use App\Http\Controllers\Api\NotificationRecepientController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\StationController;
use App\Http\Controllers\Api\TechnicalAdminController;
use App\Http\Controllers\Api\TruckController;
use App\Http\Controllers\Api\VehicleRentalRecordController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me'])->name('me');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::middleware(['role:admin|checker|technical-admin|gas-operator'])->group(function () {
        Route::get('clients', [ClientController::class, 'index']);
        Route::get('projects', [ProjectController::class, 'index']);

        Route::get('drivers', [DriverController::class, 'index']);
        Route::get('driver/check-availability/{id}', [DriverController::class, 'checkAvailability']);

        Route::get('vendors', [VendorController::class, 'index']);

        Route::get('trucks', [TruckController::class, 'index']);
        Route::get('truck/check-availability/{id}', [TruckController::class, 'checkAvailability']);

        Route::get('heavy-vehicles', [HeavyVehicleController::class, 'index']);
        Route::get('heavy-vehicle/check-availability/{id}', [HeavyVehicleController::class, 'checkAvailability']);

        Route::get('vehicle-rental-records', [VehicleRentalRecordController::class, 'index']);
        Route::get('vehicle-rental-records/read/due', [VehicleRentalRecordController::class, 'getDueVehicleRentalRecords']);

        Route::get('materials', [MaterialController::class, 'index']);

        Route::get('stations', [StationController::class, 'index']);
        Route::get('station/check-availability/{id}', [StationController::class, 'checkAvailability']);

        Route::get('technical-admins', [TechnicalAdminController::class, 'index']);

        Route::get('gas-operators', [GasOperatorController::class, 'index']);

        Route::get('checkers', [CheckerController::class, 'index']);

        Route::get('fuel-logs', [FuelLogController::class, 'index']);

        Route::get('fuel-log-error-logs', [FuelLogErrorLogController::class, 'index']);

        Route::get('material-movements', [MaterialMovementController::class, 'index']);
        Route::get('material-movements/read/by-truck/{truckId}', [MaterialMovementController::class, 'getMaterialMovementByTruck']);
        Route::get('material-movements/read/statistic-truck-per-day-by-station', [MaterialMovementController::class, 'getStatisticTruckPerDayByStation']);
        Route::get('material-movements/read/statistic-ritage-per-day-by-station', [MaterialMovementController::class, 'getStatisticRitagePerDayByStation']);
        Route::get('material-movements/read/statistic-measurement-volume-by-station', [MaterialMovementController::class, 'getStatisticMeasurementVolumeByStation']);
        Route::get('material-movements/read/statistic-ritage-volume-by-station', [MaterialMovementController::class, 'getStatisticRitageVolumeByStation']);
        Route::get('material-movements/read/ratio-measurement-by-ritage', [MaterialMovementController::class, 'getRatioMeasurementByRitage']);

        Route::get('material-movement-error-logs', [MaterialMovementErrorLogController::class, 'index']);

        Route::get('material-movement-solid-volume-estimates', [MaterialMovementController::class, 'index']);

        Route::get('notification-recepients', [NotificationRecepientController::class, 'index']);

        Route::get('activity-logs', [ActivityLogController::class, 'index']);

        Route::get('commons/read/date-periods', [CommonController::class, 'getDatePeriods']);
        Route::get('commons/read/aggregate-functions', [CommonController::class, 'getAggregateFunctions']);
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('client', [ClientController::class, 'store']);
    Route::get('client/{id}', [ClientController::class, 'show']);
    Route::post('client/{id}', [ClientController::class, 'update']);
    Route::delete('client/{id}', [ClientController::class, 'destroy']);

    Route::post('project', [ProjectController::class, 'store']);
    Route::get('project/{id}', [ProjectController::class, 'show']);
    Route::get('project/read/status', [ProjectController::class, 'getProjectStatus']);
    Route::post('project/{id}', [ProjectController::class, 'update']);
    Route::delete('project/{id}', [ProjectController::class, 'destroy']);

    Route::post('driver', [DriverController::class, 'store']);
    Route::get('driver/{id}', [DriverController::class, 'show']);
    Route::post('driver/{id}', [DriverController::class, 'update']);
    Route::post('driver/active/{id}', [DriverController::class, 'updateActiveStatus']);
    Route::delete('driver/{id}', [DriverController::class, 'destroy']);

    Route::post('vendor', [VendorController::class, 'store']);
    Route::get('vendor/{id}', [VendorController::class, 'show']);
    Route::post('vendor/{id}', [VendorController::class, 'update']);
    Route::post('vendor/active/{id}', [VendorController::class, 'updateActiveStatus']);
    Route::delete('vendor/{id}', [VendorController::class, 'destroy']);

    Route::post('truck', [TruckController::class, 'store']);
    Route::get('truck/{id}', [TruckController::class, 'show']);
    Route::post('truck/{id}', [TruckController::class, 'update']);
    Route::post('truck/active/{id}', [TruckController::class, 'updateActiveStatus']);
    Route::delete('truck/{id}', [TruckController::class, 'destroy']);

    Route::post('heavy-vehicle', [HeavyVehicleController::class, 'store']);
    Route::get('heavy-vehicle/{id}', [HeavyVehicleController::class, 'show']);
    Route::post('heavy-vehicle/{id}', [HeavyVehicleController::class, 'update']);
    Route::post('heavy-vehicle/active/{id}', [HeavyVehicleController::class, 'updateActiveStatus']);
    Route::delete('heavy-vehicle/{id}', [HeavyVehicleController::class, 'destroy']);

    Route::post('vehicle-rental-record', [VehicleRentalRecordController::class, 'store']);
    Route::get('vehicle-rental-record/{id}', [VehicleRentalRecordController::class, 'show']);
    Route::get('vehicle-rental-record/read/status', [VehicleRentalRecordController::class, 'getVehicleRentalRecordStatus']);
    Route::post('vehicle-rental-record/{id}', [VehicleRentalRecordController::class, 'update']);
    Route::post('vehicle-rental-record/payment/{id}', [VehicleRentalRecordController::class, 'updateRentalPaymentStatus']);
    Route::delete('vehicle-rental-record/{id}', [VehicleRentalRecordController::class, 'destroy']);

    Route::post('material', [MaterialController::class, 'store']);
    Route::get('material/{id}', [MaterialController::class, 'show']);
    Route::post('material/{id}', [MaterialController::class, 'update']);
    Route::delete('material/{id}', [MaterialController::class, 'destroy']);

    Route::post('station', [StationController::class, 'store']);
    Route::get('station/{id}', [StationController::class, 'show']);
    Route::get('station/read/categories', [StationController::class, 'getStationCategory']);
    Route::post('station/{id}', [StationController::class, 'update']);
    Route::post('station/active/{id}', [StationController::class, 'updateActiveStatus']);
    Route::delete('station/{id}', [StationController::class, 'destroy']);

    Route::post('technical-admin', [TechnicalAdminController::class, 'store']);
    Route::get('technical-admin/{id}', [TechnicalAdminController::class, 'show']);
    Route::post('technical-admin/{id}', [TechnicalAdminController::class, 'update']);
    Route::post('technical-admin/active/{id}', [TechnicalAdminController::class, 'updateActiveStatus']);
    Route::delete('technical-admin/{id}', [TechnicalAdminController::class, 'destroy']);

    Route::post('gas-operator', [GasOperatorController::class, 'store']);
    Route::get('gas-operator/{id}', [GasOperatorController::class, 'show']);
    Route::post('gas-operator/{id}', [GasOperatorController::class, 'update']);
    Route::post('gas-operator/active/{id}', [GasOperatorController::class, 'updateActiveStatus']);
    Route::delete('gas-operator/{id}', [GasOperatorController::class, 'destroy']);

    Route::post('checker', [CheckerController::class, 'store']);
    Route::get('checker/{id}', [CheckerController::class, 'show']);
    Route::post('checker/{id}', [CheckerController::class, 'update']);
    Route::post('checker/active/{id}', [CheckerController::class, 'updateActiveStatus']);
    Route::delete('checker/{id}', [CheckerController::class, 'destroy']);

    Route::post('fuel-log/truck', [FuelLogController::class, 'storeTruck']);
    Route::post('fuel-log/heavy-vehicle', [FuelLogController::class, 'storeHeavyVehicle']);
    Route::get('fuel-log/{id}', [FuelLogController::class, 'show']);
    Route::get('fuel-log/read/fuel-types', [FuelLogController::class, 'getFuelType']);
    Route::post('fuel-log/truck/{id}', [FuelLogController::class, 'updateTruck']);
    Route::post('fuel-log/heavy-vehicle/{id}', [FuelLogController::class, 'updateHeavyVehicle']);

    Route::post('fuel-log-error-log', [FuelLogErrorLogController::class, 'store']);
    Route::get('fuel-log-error-log/{id}', [FuelLogErrorLogController::class, 'show']);
    Route::post('fuel-log/{id}', [FuelLogController::class, 'update']);
    Route::delete('fuel-log/{id}', [FuelLogController::class, 'destroy']);

    Route::post('material-movement', [MaterialMovementController::class, 'store']);
    Route::get('material-movement/{id}', [MaterialMovementController::class, 'show']);
    Route::post('material-movement/{id}', [MaterialMovementController::class, 'update']);
    Route::delete('material-movement/{id}', [MaterialMovementController::class, 'destroy']);

    Route::post('material-movement-solid-volume-estimate', [MaterialMovementSolidVolumeEstimateController::class, 'store']);
    Route::get('material-movement-solid-volume-estimate/{id}', [MaterialMovementSolidVolumeEstimateController::class, 'show']);
    Route::post('material-movement-solid-volume-estimate/{id}', [MaterialMovementSolidVolumeEstimateController::class, 'update']);
    Route::delete('material-movement-solid-volume-estimate/{id}', [MaterialMovementSolidVolumeEstimateController::class, 'destroy']);

    Route::post('notification-recepient', [NotificationRecepientController::class, 'store']);
    Route::get('notification-recepient/{id}', [NotificationRecepientController::class, 'show']);
    Route::post('notification-recepient/{id}', [NotificationRecepientController::class, 'update']);
    Route::post('notification-recepient/active/{id}', [NotificationRecepientController::class, 'updateActiveStatus']);
    Route::delete('notification-recepient/{id}', [NotificationRecepientController::class, 'destroy']);

    Route::post('fuel-log-error-log/truck', [FuelLogErrorLogController::class, 'storeTruck']);
    Route::post('fuel-log-error-log/heavy-vehicle', [FuelLogErrorLogController::class, 'storeHeavyVehicle']);
    Route::get('fuel-log-error-log/{id}', [FuelLogErrorLogController::class, 'show']);

    Route::post('material-movement-error-log', [MaterialMovementErrorLogController::class, 'store']);
    Route::get('material-movement-error-log/{id}', [MaterialMovementErrorLogController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:checker'])->group(function () {
    Route::post('checker/material-movement/store', [MaterialMovementController::class, 'store']);

    Route::post('checker/material-movement-error-log/store', [MaterialMovementErrorLogController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:gas-operator'])->group(function () {
    Route::post('gas-operator/fuel-log/truck/store', [FuelLogController::class, 'storeTruck']);
    Route::post('gas-operator/fuel-log/heavy-vehicle/store', [FuelLogController::class, 'storeHeavyVehicle']);

    Route::post('gas-operator/fuel-log-error-log/truck/store', [FuelLogErrorLogController::class, 'storeTruck']);
    Route::post('gas-operator/fuel-log-error-log/heavy-vehicle/store', [FuelLogErrorLogController::class, 'storeHeavyVehicle']);
});

Route::middleware(['auth:sanctum', 'role:technical-admin'])->group(function () {
    Route::get('technical-admin/material-movement/{id}', [MaterialMovementController::class, 'show']);
    Route::post('technical-admin/material-movement/update/{id}', [MaterialMovementController::class, 'update']);
});
