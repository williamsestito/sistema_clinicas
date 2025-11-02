<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSchedule
 */
class Schedule extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'professional_id',
        'weekday',
        'start_time',
        'end_time',
        'slot_min',
        'buffer_before',
        'buffer_after',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Tenant (Clínica)
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
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Filtrar por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Filtrar por profissional
    public function scopeOfProfessional($query, int $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    // 🔍 Filtrar por dia da semana
    public function scopeWeekday($query, int $weekday)
    {
        return $query->where('weekday', $weekday);
    }

    // 🔍 Ordenar por dia e hora
    public function scopeOrdered($query)
    {
        return $query->orderBy('weekday')->orderBy('start_time');
    }

    /**
     * Helpers e Accessors
     * ======================================
     */

    // 📅 Nome do dia da semana
    public function getWeekdayLabelAttribute(): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return $days[$this->weekday] ?? 'Desconhecido';
    }

    // ⏰ Intervalo de horário formatado
    public function getTimeRangeAttribute(): string
    {
        return sprintf('%s - %s', 
            $this->start_time ? $this->start_time->format('H:i') : '??',
            $this->end_time ? $this->end_time->format('H:i') : '??'
        );
    }

    // ⏳ Duração total em minutos
    public function getTotalDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) return null;
        return $this->end_time->diffInMinutes($this->start_time);
    }

    // 🧩 Slots disponíveis no dia
    public function generateSlots(): array
    {
        if (!$this->start_time || !$this->end_time) return [];

        $slots = [];
        $start = $this->start_time->copy()->addMinutes($this->buffer_before);
        $end = $this->end_time->copy()->subMinutes($this->buffer_after);

        while ($start->lessThan($end)) {
            $slotEnd = $start->copy()->addMinutes($this->slot_min);
            if ($slotEnd->greaterThan($end)) break;

            $slots[] = [
                'start' => $start->format('H:i'),
                'end' => $slotEnd->format('H:i'),
            ];

            $start = $slotEnd;
        }

        return $slots;
    }
}
