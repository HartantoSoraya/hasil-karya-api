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
        Route::get('material-movements/read/statistic-truck-per-day-by-station', [MaterialMovementController::class, 'getStatisticTruckPerDayByStation']);
        Route::get('material-movements/read/statistic-ritage-per-day-by-station', [MaterialMovementController::class, 'getStatisticRitagePerDayByStation']);
        Route::get('material-movements/read/statistic-measurement-volume-by-station', [MaterialMovementController::class, 'getStatisticMeasurementVolumeByStation']);
        Route::get('material-movements/read/statistic-ritage-volume-by-station', [MaterialMovementController::class, 'getStatisticRitageVolumeByStation']);
        Route::get('material-movements/read/ratio-measurement-by-ritage', [MaterialMovementController::class, 'getRatioMeasurementByRitage']);

        Route::get('material-movement-error-logs', [MaterialMovementErrorLogController::class, 'index']);

        Route::get('notification-recepients', [NotificationRecepientController::class, 'index']);

        Route::get('activity-logs', [ActivityLogController::class, 'index']);

        Route::get('commons/read/date-periods', [CommonController::class, 'getDatePeriods']);
        Route::get('commons/read/aggregate-functions', [CommonController::class, 'getAggregateFunctions']);
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('client', ClientController::class);
    Route::post('client/{id}', [ClientController::class, 'update']);

    Route::apiResource('project', ProjectController::class);
    Route::post('project/{id}', [ProjectController::class, 'update']);
    Route::get('project/read/status', [ProjectController::class, 'getProjectStatus']);

    Route::apiResource('driver', DriverController::class);
    Route::post('driver/{id}', [DriverController::class, 'update']);
    Route::post('driver/active/{id}', [DriverController::class, 'updateActiveStatus']);

    Route::apiResource('vendor', VendorController::class);
    Route::post('vendor/{id}', [VendorController::class, 'update']);
    Route::post('vendor/active/{id}', [VendorController::class, 'updateActiveStatus']);

    Route::apiResource('truck', TruckController::class);
    Route::post('truck/{id}', [TruckController::class, 'update']);
    Route::post('truck/active/{id}', [TruckController::class, 'updateActiveStatus']);

    Route::apiResource('heavy-vehicle', HeavyVehicleController::class);
    Route::post('heavy-vehicle/{id}', [HeavyVehicleController::class, 'update']);
    Route::post('heavy-vehicle/active/{id}', [HeavyVehicleController::class, 'updateActiveStatus']);

    Route::apiResource('vehicle-rental-record', VehicleRentalRecordController::class);
    Route::post('vehicle-rental-record/{id}', [VehicleRentalRecordController::class, 'update']);
    Route::get('vehicle-rental-record/read/status', [VehicleRentalRecordController::class, 'getVehicleRentalRecordStatus']);
    Route::post('vehicle-rental-record/payment/{id}', [VehicleRentalRecordController::class, 'updateRentalPaymentStatus']);

    Route::apiResource('material', MaterialController::class);
    Route::post('material/{id}', [MaterialController::class, 'update']);

    Route::apiResource('station', StationController::class);
    Route::post('station/{id}', [StationController::class, 'update']);
    Route::get('station/read/categories', [StationController::class, 'getStationCategory']);
    Route::post('station/active/{id}', [StationController::class, 'updateActiveStatus']);

    Route::apiResource('technical-admin', TechnicalAdminController::class);
    Route::post('technical-admin/{id}', [TechnicalAdminController::class, 'update']);
    Route::post('technical-admin/active/{id}', [TechnicalAdminController::class, 'updateActiveStatus']);

    Route::apiResource('gas-operator', GasOperatorController::class);
    Route::post('gas-operator/{id}', [GasOperatorController::class, 'update']);
    Route::post('gas-operator/active/{id}', [GasOperatorController::class, 'updateActiveStatus']);

    Route::apiResource('checker', CheckerController::class);
    Route::post('checker/{id}', [CheckerController::class, 'update']);
    Route::post('checker/active/{id}', [CheckerController::class, 'updateActiveStatus']);

    Route::post('fuel-log/truck', [FuelLogController::class, 'storeTruck']);
    Route::post('fuel-log/heavy-vehicle', [FuelLogController::class, 'storeHeavyVehicle']);
    Route::get('fuel-log/read/fuel-types', [FuelLogController::class, 'getFuelType']);
    Route::post('fuel-log/truck/{id}', [FuelLogController::class, 'updateTruck']);
    Route::post('fuel-log/heavy-vehicle/{id}', [FuelLogController::class, 'updateHeavyVehicle']);

    Route::apiResource('fuel-log', FuelLogController::class)->except(['store', 'update']);
    Route::post('fuel-log/{id}', [FuelLogController::class, 'update']);

    Route::apiResource('material-movement', MaterialMovementController::class);
    Route::post('material-movement/{id}', [MaterialMovementController::class, 'update']);

    Route::apiResource('notification-recepient', NotificationRecepientController::class);
    Route::post('notification-recepient/{id}', [NotificationRecepientController::class, 'update']);
    Route::post('notification-recepient/active/{id}', [NotificationRecepientController::class, 'updateActiveStatus']);

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
