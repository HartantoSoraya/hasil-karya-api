<?php

namespace App\Models;

use App\Traits\UUID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaterialMovement extends Model
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
        'remarks',
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
                'remarks',
            ])->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}")
            ->useLogName('MaterialMovement');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function checker()
    {
        return $this->belongsTo(Checker::class);
    }

    // public function getFormattedDateAttribute()
    // {
    //     $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->date);

    //     return $date->isoFormat('DD MMMM YYYY');
    // }
}
