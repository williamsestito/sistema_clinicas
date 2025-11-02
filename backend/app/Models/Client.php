<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperClient
 */
class Client extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'birthdate',
        'consent_marketing',
        'notes',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'birthdate' => 'date',
        'consent_marketing' => 'boolean',
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

    // 🔹 Agendamentos do cliente
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Filtro por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Busca por nome, e-mail ou telefone
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // 🔍 Clientes com consentimento para marketing
    public function scopeMarketingOptIn($query)
    {
        return $query->where('consent_marketing', true);
    }

    // 🔍 Ordenar por nome
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * Helpers e Accessors
     * ======================================
     */

    // 📅 Idade do cliente
    public function getAgeAttribute(): ?int
    {
        return $this->birthdate ? $this->birthdate->age : null;
    }

    // 📱 Telefone formatado
    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) return '-';
        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) === 11) {
            return sprintf('(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7)
            );
        }

        if (strlen($digits) === 10) {
            return sprintf('(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 4),
                substr($digits, 6)
            );
        }

        return $this->phone;
    }

    // 🧾 Status de consentimento
    public function getMarketingStatusLabelAttribute(): string
    {
        return $this->consent_marketing ? 'Aceitou comunicações' : 'Não aceitou';
    }
}
