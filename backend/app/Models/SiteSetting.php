<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin IdeHelperSiteSetting
 */
class SiteSetting extends Model
{
    use HasFactory;

    const CREATED_AT = null;

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'tenant_id',
        'site_title',
        'tagline',
        'about_title',
        'about_text',
        'contact_phone',
        'contact_email',
        'address',
        'instagram_url',
        'facebook_url',
        'whatsapp_url',
        'active',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'active' => 'boolean',
        'updated_at' => 'datetime',
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

    /**
     * Scopes
     * ======================================
     */

    // 🔍 Apenas configurações ativas
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Accessors e atributos customizados
     * ======================================
     */

    // 🧭 Nome completo para exibição
    public function fullTitle(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) =>
                trim(($attributes['site_title'] ?? '') . ' - ' . ($attributes['tagline'] ?? ''))
        );
    }

    // 📞 Formatar telefone (padrão nacional)
    public function getPhoneFormattedAttribute(): string
    {
        $phone = preg_replace('/\D/', '', $this->contact_phone ?? '');
        if (strlen($phone) === 11) {
            return sprintf('(%s) %s-%s', substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7));
        }
        return $this->contact_phone ?? '';
    }

    // 💬 Gera link de WhatsApp clicável
    public function whatsappLink(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) =>
                isset($attributes['whatsapp_url'])
                    ? $attributes['whatsapp_url']
                    : ($this->contact_phone
                        ? 'https://wa.me/55' . preg_replace('/\D/', '', $this->contact_phone)
                        : null)
        );
    }

    // 🗺️ Formata endereço de exibição
    public function getAddressFormattedAttribute(): string
    {
        return $this->address ? ucfirst($this->address) : 'Endereço não informado';
    }

    // 🔗 Lista de redes sociais disponíveis
    public function getSocialLinksAttribute(): array
    {
        return array_filter([
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'whatsapp' => $this->whatsappLink->get(),
        ]);
    }

    /**
     * Helpers
     * ======================================
     */

    // 🔄 Atualiza ou cria as configurações do tenant
    public static function updateForTenant(int $tenantId, array $data): self
    {
        return self::updateOrCreate(['tenant_id' => $tenantId], $data);
    }

    // 🔍 Recupera configurações do tenant
    public static function getForTenant(int $tenantId): ?self
    {
        return self::ofTenant($tenantId)->first();
    }

    // ⚙️ Retorna configurações públicas formatadas (para API pública)
    public function toPublicArray(): array
    {
        return [
            'title' => $this->site_title,
            'tagline' => $this->tagline,
            'about' => [
                'title' => $this->about_title,
                'text' => $this->about_text,
            ],
            'contact' => [
                'phone' => $this->phone_formatted,
                'email' => $this->contact_email,
                'address' => $this->address_formatted,
            ],
            'social' => $this->social_links,
            'active' => $this->active,
        ];
    }
}
