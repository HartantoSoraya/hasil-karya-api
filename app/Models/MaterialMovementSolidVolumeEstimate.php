<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MaterialMovementSolidVolumeEstimate extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, UUID;

    protected $fillable = [
        'code',
        'date',
        'station_id',
        'solid_volume_estimate',
        'remarks',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'date',
                'station_id',
                'solid_volume_estimate',
                'remarks',
            ])->setDescriptionForEvent(fn (string $eventName) => "This model has been {$eventName}")
            ->useLogName('MaterialMovementSolidVolumeEstimate');
    }

    protected $casts = [
        'date' => 'datetime',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
