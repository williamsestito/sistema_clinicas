<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchedulePeriodDay extends Model
{
    use HasFactory;

    protected $table = 'schedule_period_days';

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'period_id',
        'weekday',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'duration',
        'available',
    ];

    protected $casts = [
        'weekday'     => 'integer',
        'duration'    => 'integer',
        'available'   => 'boolean',
    ];

    public function period()
    {
        return $this->belongsTo(SchedulePeriod::class, 'period_id');
    }
}
