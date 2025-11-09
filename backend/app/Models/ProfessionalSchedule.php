<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id',
        'day_of_week',
        'available',
        'start_hour',
        'end_hour',
        'break_start',
        'break_end',
        'duration_min'
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
