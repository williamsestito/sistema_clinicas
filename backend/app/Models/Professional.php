<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin IdeHelperProfessional
 */
class Professional extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'specialty',
        'bio',
        'photo_url',
        'active',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'active' => 'boolean',
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

    // 🔹 Usuário vinculado (login e permissões)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔹 Serviços oferecidos
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // 🔹 Agendamentos
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // 🔹 Agenda fixa (dias e horários de atendimento)
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // 🔹 Exceções de agenda (feriados, bloqueios, etc.)
    public function exceptions()
    {
        return $this->hasMany(ScheduleException::class);
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Profissionais ativos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Filtro por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Busca genérica (nome, especialidade, email)
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('specialty', 'like', "%{$term}%")
              ->orWhereHas('user', fn($uq) =>
                    $uq->where('name', 'like', "%{$term}%")
                       ->orWhere('email', 'like', "%{$term}%")
                );
        });
    }

    // 🔍 Ordenar por nome do usuário
    public function scopeOrdered($query)
    {
        return $query->join('users', 'professionals.user_id', '=', 'users.id')
                     ->orderBy('users.name')
                     ->select('professionals.*');
    }

    /**
     * Helpers e Accessors
     * ======================================
     */

    // 🧍‍♂️ Nome do profissional (via User)
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'Sem nome';
    }

    // ✉️ E-mail do profissional
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    // 🖼️ URL completa da foto
    public function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? (preg_match('/^https?:\/\//', $value)
                    ? $value
                    : asset('storage/' . ltrim($value, '/')))
                : asset('images/default-professional.jpg')
        );
    }

    // ⚙️ Label de status
    public function getStatusLabelAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }

    // 🩺 Nome e especialidade formatados
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->name}" . ($this->specialty ? " ({$this->specialty})" : '');
    }
}
