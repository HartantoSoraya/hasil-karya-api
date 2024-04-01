<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaterialMovementErrorLog extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, UUID;

    protected $fillable = [
        'code',
        'driver_id',
        'truck_id',
        'station_id',
        'checker_id',
        'date',
        'truck_capacity',
        'observation_ratio',
        'solid_ratio',
        'remarks',
        'error_log',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'driver_id',
                'truck_id',
                'station_id',
                'checker_id',
                'date',
                'truck_capacity',
                'observation_ratio',
                'solid_ratio',
                'remarks',
                'error_log',
            ])->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}")
            ->useLogName('MaterialMovementErrorLog');
    }
}
