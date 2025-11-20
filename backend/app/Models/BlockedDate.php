<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BlockedDate extends Model
{
    use HasFactory;

    protected $table = 'blocked_dates';

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'date',
        'reason',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES ÚTEIS
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra pelo tenant do usuário logado.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Filtra pelo profissional.
     */
    public function scopeForProfessional($query, $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    /**
     * Ordenação padrão usada no sistema.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('date', 'desc');
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS / MUTATORS
    |--------------------------------------------------------------------------
    */

    /**
     * Formata automaticamente a exibição da data (pt-BR).
     */
    public function getDateFormattedAttribute()
    {
        return Carbon::parse($this->date)->format('d/m/Y');
    }

    /**
     * Retorna o motivo ou "-" caso não tenha.
     */
    public function getReasonCleanAttribute()
    {
        return $this->reason ?: '-';
    }
}
