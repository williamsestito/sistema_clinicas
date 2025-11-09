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
        'end_at' => 'datetime',
    ];

    // Relacionamento com tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // Relacionamento com cliente (tabela users)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Relacionamento com profissional
    public function professional()
    {
        return $this->belongsTo(Professional::class, 'professional_id');
    }

    // Relacionamento com serviço
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    // Relacionamento com logs do agendamento
    public function logs()
    {
        return $this->hasMany(AppointmentLog::class);
    }
}
