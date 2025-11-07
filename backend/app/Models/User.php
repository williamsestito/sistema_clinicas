<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos que podem ser atribuídos em massa
     */
    protected $fillable = [
        'tenant_id',
        'role',
        'name',
        'social_name',
        'social_name_text',
        'birth_date',
        'document',
        'rg',
        'civil_status',
        'gender',
        'email',
        'phone',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'password',
        'active',
    ];

    /**
     * Campos ocultos ao serializar o model
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipos de dados automáticos
     */
    protected $casts = [
        'active' => 'boolean',
        'social_name' => 'boolean',
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relação com Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relações auxiliares (mantidas)
     */
    public function professional()
    {
        return $this->hasOne(Professional::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'professional_id');
    }

    public function appointmentLogs()
    {
        return $this->hasMany(AppointmentLog::class, 'changed_by_user_id');
    }

    /**
     * Hash automático de senha
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value && !Str::startsWith($value, '$2y$')
                ? Hash::make($value)
                : $value
        );
    }

    /**
     * Formatação de nome e telefone
     */
    public function getDisplayNameAttribute(): string
    {
        return ucwords($this->name);
    }

    public function getPhoneFormattedAttribute(): ?string
    {
        $num = preg_replace('/\D/', '', $this->phone ?? '');
        if (strlen($num) === 11) {
            return sprintf('(%s) %s-%s', substr($num, 0, 2), substr($num, 2, 5), substr($num, 7));
        }
        return $this->phone;
    }

    /**
     * Escopos e filtros
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Métodos de papéis
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    public function isProfessional(): bool
    {
        return $this->role === 'professional';
    }

    public function isFrontdesk(): bool
    {
        return $this->role === 'frontdesk';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Token de API
     */
    public function generateToken(string $device = 'web'): string
    {
        $this->tokens()->delete();
        return $this->createToken("{$device}_token")->plainTextToken;
    }

    public function revokeTokens(): void
    {
        $this->tokens()->delete();
    }

    /**
     * Rótulo legível da função
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'owner' => 'Proprietário',
            'admin' => 'Administrador',
            'professional' => 'Profissional',
            'frontdesk' => 'Recepção',
            'client' => 'Cliente',
            default => ucfirst($this->role ?? 'Usuário'),
        };
    }

    /**
     * Representação pública segura
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'email' => $this->email,
            'role' => $this->role,
            'role_label' => $this->role_label,
            'phone' => $this->phone_formatted,
            'active' => $this->active,
            'city' => $this->city,
            'state' => $this->state,
        ];
    }
}
