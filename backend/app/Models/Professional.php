<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'specialty',
        'bio',
        'photo_url',
        'active',
        'show_prices',
        'default_start_hour',
        'default_end_hour',
        'default_consultation_time'
    ];

    // 🔗 Relacionamentos
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function schedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    public function blockedDates()
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
