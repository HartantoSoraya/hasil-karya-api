<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FuelLogErrorLog extends Model
{
    use HasFactory,LogsActivity, SoftDeletes, UUID;

    protected $fillable = [
        'code',
        'date',
        'truck_id',
        'heavy_vehicle_id',
        'driver_id',
        'station_id',
        'gas_operator_id',
        'fuel_type',
        'volume',
        'odometer',
        'hourmeter',
        'remarks',
        'error_log',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'date', 'truck_id', 'heavy_vehicle_id', 'driver_id', 'station_id', 'gas_operator_id', 'fuel_type', 'volume', 'odometer', 'hourmeter', 'remarks', 'error_log'])
            ->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}")
            ->useLogName('FuelLogErrorLog');
    }
}
