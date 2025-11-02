<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperTenant
 */
class Tenant extends Model
{
    use HasFactory;

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'name',
        'cnpj',
        'im',
        'owner_user_id',
        'logo_url',
        'primary_color',
        'secondary_color',
        'settings',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Usuário dono (owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    // 🔹 Usuários vinculados à clínica
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // 🔹 Clientes
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    // 🔹 Profissionais
    public function professionals()
    {
        return $this->hasMany(Professional::class);
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

    // 🔹 Configurações de site público
    public function siteSettings()
    {
        return $this->hasOne(SiteSetting::class);
    }

    /**
     * Accessors e atributos computados
     * ======================================
     */

    // 🖼️ Logo da clínica (URL completa)
    public function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value
                    ? (preg_match('/^https?:\/\//', $value)
                        ? $value
                        : Storage::url($value))
                    : asset('images/default-logo.png')
        );
    }

    // 🎨 Cores com fallback padrão
    public function getPrimaryColorAttribute($value): string
    {
        return $value ?: '#004d40';
    }

    public function getSecondaryColorAttribute($value): string
    {
        return $value ?: '#009688';
    }

    // 🧠 Retorna nome formatado para exibição
    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->name);
    }

    // 🗓️ Data de criação formatada
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at?->format('d/m/Y H:i') ?? '-';
    }

    /**
     * Scopes
     * ======================================
     */

    // 🔍 Busca por nome
    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;
        return $query->where('name', 'like', "%{$term}%");
    }

    // 🔍 Ordenar por nome
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Helpers
     * ======================================
     */

    // 🏢 Criação de tenant com usuário proprietário
    public static function createWithOwner(array $tenantData, array $ownerData): self
    {
        $tenant = self::create($tenantData);
        $owner = $tenant->users()->create(array_merge($ownerData, [
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]));
        $tenant->update(['owner_user_id' => $owner->id]);
        return $tenant->fresh(['owner']);
    }

    // ⚙️ Atualizar configurações (JSON)
    public function updateSettings(array $data): self
    {
        $settings = $this->settings ?? [];
        $this->settings = array_merge($settings, $data);
        $this->save();
        return $this;
    }

    // 🧩 Retornar paleta de cores do tenant
    public function palette(): array
    {
        return [
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
        ];
    }

    // 🔍 Localizar tenant por domínio ou subdomínio
    public static function findByDomain(string $host): ?self
    {
        return self::whereJsonContains('settings->domains', $host)->first();
    }
}
