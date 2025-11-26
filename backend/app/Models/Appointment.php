<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'professional_id',
        'service_id', 
        'start_at',
        'end_at',
        'status',
        'source',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class, 'professional_id');
    }

    // 🔥 Agora explicitamente nullable
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id')->withDefault();
    }

    public function logs()
    {
        return $this->hasMany(AppointmentLog::class);
    }
}
