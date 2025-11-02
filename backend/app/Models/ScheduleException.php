<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperScheduleException
 */
class ScheduleException extends Model
{
    use HasFactory;

    /**
     * Atributos preenchíveis em massa.
     */
    protected $fillable = [
        'tenant_id',
        'professional_id',
        'date',
        'type',
        'start_time',
        'end_time',
        'reason',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Tenant (clínica)
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // 🔹 Profissional
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    /**
     * Scopes e filtros úteis
     * ======================================
     */

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Filtrar por profissional
    public function scopeOfProfessional($query, int $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    // 🔍 Filtrar por data
    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    // 🔍 Filtrar por tipo (block, holiday, special)
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accessors e Helpers
     * ======================================
     */

    // 🏷️ Nome legível do tipo
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'block' => 'Bloqueio de horário',
            'holiday' => 'Feriado',
            'special' => 'Atendimento especial',
            default => ucfirst($this->type ?? '-'),
        };
    }

    // 📅 Data formatada
    public function getDateFormattedAttribute(): string
    {
        return $this->date ? $this->date->format('d/m/Y') : '-';
    }

    // ⏰ Horário formatado
    public function getTimeRangeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) return 'Dia inteiro';
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    // ⚙️ Texto completo resumido
    public function getSummaryAttribute(): string
    {
        return "{$this->getTypeLabelAttribute()} em {$this->getDateFormattedAttribute()} ({$this->getTimeRangeAttribute()})";
    }

    /**
     * Métodos auxiliares
     * ======================================
     */

    // 📆 Verifica se é um dia inteiro
    public function isFullDay(): bool
    {
        return !$this->start_time && !$this->end_time;
    }

    // 🕒 Verifica se afeta um horário específico
    public function affectsTime(string $time): bool
    {
        if ($this->isFullDay()) return true;

        return $time >= $this->start_time->format('H:i') &&
               $time <= $this->end_time->format('H:i');
    }
}
