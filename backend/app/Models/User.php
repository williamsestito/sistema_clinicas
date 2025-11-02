<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'tenant_id',
        'role',
        'name',
        'email',
        'phone',
        'password',
        'active',
    ];

    /**
     * Campos ocultos em respostas JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'active' => 'boolean',
        'email_verified_at' => 'datetime',
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

    // 🔹 Profissional vinculado (se aplicável)
    public function professional()
    {
        return $this->hasOne(Professional::class);
    }

    // 🔹 Agendamentos criados (como profissional)
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'professional_id');
    }

    // 🔹 Logs de agendamentos
    public function appointmentLogs()
    {
        return $this->hasMany(AppointmentLog::class, 'changed_by_user_id');
    }

    /**
     * Accessors e mutators
     * ======================================
     */

    // 🔒 Hash automático da senha
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value && !Str::startsWith($value, '$2y$')
                ? Hash::make($value)
                : $value
        );
    }

    // 🧠 Nome formatado
    public function getDisplayNameAttribute(): string
    {
        return ucwords($this->name);
    }

    // 📞 Telefone formatado
    public function getPhoneFormattedAttribute(): ?string
    {
        $num = preg_replace('/\D/', '', $this->phone ?? '');
        if (strlen($num) === 11) {
            return sprintf('(%s) %s-%s', substr($num, 0, 2), substr($num, 2, 5), substr($num, 7));
        }
        return $this->phone;
    }

    /**
     * Scopes (filtros reutilizáveis)
     * ======================================
     */

    // 🔍 Apenas ativos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Filtrar por papel (role)
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Helpers e métodos de acesso
     * ======================================
     */

    // 👤 Verifica se o usuário é administrador do tenant
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    // 👨‍⚕️ Verifica se é profissional
    public function isProfessional(): bool
    {
        return $this->role === 'professional';
    }

    // 🧾 Verifica se é recepcionista
    public function isFrontdesk(): bool
    {
        return $this->role === 'frontdesk';
    }

    // 🧍‍♂️ Verifica se é cliente
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // 🔑 Gera e retorna token Sanctum
    public function generateToken(string $device = 'web'): string
    {
        $this->tokens()->delete(); // remove tokens anteriores
        return $this->createToken("{$device}_token")->plainTextToken;
    }

    // 🚪 Revoga todos os tokens (logout)
    public function revokeTokens(): void
    {
        $this->tokens()->delete();
    }

    // 🧩 Retorna papel formatado
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
     * Retorna dados públicos (para API frontend)
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
        ];
    }
}
