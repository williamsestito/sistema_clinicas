<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'professional_id',
        'period_id',
        'weekday',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'slot_min', // duração do slot
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com o período
     */
    public function period()
    {
        return $this->belongsTo(SchedulePeriod::class, 'period_id');
    }

    /**
     * Gera todos os horários (slots) disponíveis do dia
     */
public function generateSlots()
{
    $slots = [];

    // Proteções obrigatórias
    if (
        !$this->start_time ||
        !$this->end_time ||
        !$this->slot_min ||
        $this->slot_min <= 0
    ) {
        return $slots;
    }

    $start = Carbon::parse($this->start_time);
    $end   = Carbon::parse($this->end_time);

    // Intervalo
    $breakStart = $this->break_start ? Carbon::parse($this->break_start) : null;
    $breakEnd   = $this->break_end ? Carbon::parse($this->break_end) : null;

    // Segurança extra: previne loop infinito
    $maxIterations = 500; // ninguém tem 500 slots no dia
    $i = 0;

    while ($start < $end) {

        $i++;
        if ($i > $maxIterations) {
            break; // interrupção de emergência
        }

        $slotEnd = (clone $start)->addMinutes($this->slot_min);

        if ($slotEnd > $end) {
            break;
        }

        // pular intervalo
        if ($breakStart && $breakEnd) {
            if ($start->between($breakStart, $breakEnd) ||
                $slotEnd->between($breakStart, $breakEnd)) {
                $start = $breakEnd;
                continue;
            }
        }

        // adiciona slot
        $slots[] = [
            'start' => $start->format('H:i'),
            'end'   => $slotEnd->format('H:i'),
        ];

        // avança
        $start = $slotEnd;
    }

    return $slots;
}

}
