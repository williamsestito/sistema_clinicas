<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperAppointment
 */
class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Atributos preenchíveis em massa.
     */
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

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Clínica (Tenant)
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // 🔹 Cliente (Paciente)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // 🔹 Profissional
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    // 🔹 Serviço / Procedimento
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // 🔹 Logs de alterações de status
    public function logs()
    {
        return $this->hasMany(AppointmentLog::class);
    }

    // 🔹 Notificações enviadas
    public function notifications()
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Escopo para filtrar por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Escopo por status
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // 🔍 Escopo para buscar agendamentos futuros
    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at', 'asc');
    }

    /**
     * Helpers de status
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Retorna duração da consulta (em minutos)
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_at && $this->end_at
            ? $this->end_at->diffInMinutes($this->start_at)
            : 0;
    }

    /**
     * Retorna status formatado
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendente',
            'confirmed' => 'Confirmado',
            'done'      => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show'   => 'Não compareceu',
            default     => ucfirst($this->status),
        };
    }

    /**
     * Retorna data formatada amigável (para exibição)
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->start_at
            ? $this->start_at->format('d/m/Y H:i')
            : '-';
    }
}
