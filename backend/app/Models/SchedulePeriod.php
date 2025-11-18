<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulePeriod extends Model
{
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

    // Garante que active_days sempre seja array de inteiros
    public function getActiveDaysAttribute($value)
    {
        if (!$value) {
            return [];
        }

        return collect(json_decode($value, true))
            ->map(fn($d) => (int) $d)
            ->toArray();
    }

    // Um período tem vários horários semanais
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'period_id');
    }
}
