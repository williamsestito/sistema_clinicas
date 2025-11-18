<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulePeriodDay extends Model
{
    protected $fillable = [
        'schedule_period_id',
        'weekday',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'duration',
        'buffer_before',
        'buffer_after',
        'available',
    ];

    protected $casts = [
        'weekday'       => 'integer',
        'start_time'    => 'datetime:H:i',
        'end_time'      => 'datetime:H:i',
        'break_start'   => 'datetime:H:i',
        'break_end'     => 'datetime:H:i',
        'duration'      => 'integer',
        'buffer_before' => 'integer',
        'buffer_after'  => 'integer',
        'available'     => 'boolean',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(SchedulePeriod::class);
    }
}
