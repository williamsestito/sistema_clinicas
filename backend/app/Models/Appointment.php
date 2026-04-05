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
        'cancel_reason',
        'reschedule_reason',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Cliente (User com role=client) */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** Profissional */
    public function professional()
    {
        return $this->belongsTo(Professional::class, 'professional_id');
    }

    /** Serviço */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id')->withDefault();
    }

    /** Logs */
    public function logs()
    {
        return $this->hasMany(AppointmentLog::class);
    }
}
