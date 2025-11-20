<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchedulePeriod extends Model
{
    protected $table = 'schedule_periods';

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'start_date',
        'end_date',
        'active_days',
    ];

    protected $casts = [
        'active_days' => 'array',
        'start_date'  => 'date',
        'end_date'    => 'date',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(SchedulePeriodDay::class, 'period_id');
    }
}
