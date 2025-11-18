<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'professional_id',
        'period_id',
        'weekday',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'slot_min',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relacionamento com o período
    public function period()
    {
        return $this->belongsTo(SchedulePeriod::class, 'period_id');
    }
}
