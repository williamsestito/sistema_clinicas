<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAppointmentLog
 */
class AppointmentLog extends Model
{
    use HasFactory;

    /**
     * Atributos preenchíveis em massa.
     */
    protected $fillable = [
        'appointment_id',
        'changed_by_user_id',
        'from_status',
        'to_status',
        'note',
        'changed_at',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Agendamento relacionado
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // 🔹 Usuário que realizou a alteração
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Escopo para logs recentes
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('changed_at', 'desc')->limit($limit);
    }

    // 🔍 Escopo para filtrar por agendamento
    public function scopeForAppointment($query, int $appointmentId)
    {
        return $query->where('appointment_id', $appointmentId);
    }

    /**
     * Retorna o status anterior formatado
     */
    public function getFromStatusLabelAttribute(): string
    {
        return $this->formatStatus($this->from_status);
    }

    /**
     * Retorna o novo status formatado
     */
    public function getToStatusLabelAttribute(): string
    {
        return $this->formatStatus($this->to_status);
    }

    /**
     * Retorna data/hora formatada
     */
    public function getChangedAtFormattedAttribute(): string
    {
        return $this->changed_at
            ? $this->changed_at->format('d/m/Y H:i')
            : '-';
    }

    /**
     * Formata o status em texto legível
     */
    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'pending'   => 'Pendente',
            'confirmed' => 'Confirmado',
            'done'      => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show'   => 'Não compareceu',
            default     => ucfirst($status ?? '-'),
        };
    }
}
