<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperTestimonial
 */
class Testimonial extends Model
{
    use HasFactory;

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'tenant_id',
        'client_name',
        'rating',
        'comment',
        'photo_url',
        'visible',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'rating' => 'integer',
        'visible' => 'boolean',
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
     * Accessors e atributos customizados
     * ======================================
     */

    // 🖼️ URL completa da foto
    public function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value
                    ? (preg_match('/^https?:\/\//', $value)
                        ? $value
                        : Storage::url($value))
                    : asset('images/default-avatar.png')
        );
    }

    // ⭐️ Gera ícones de estrelas conforme nota
    public function getStarsHtmlAttribute(): string
    {
        $stars = min(max($this->rating ?? 5, 1), 5);
        return str_repeat('⭐', $stars);
    }

    // 🧠 Limita o comentário para listagens
    public function getExcerptAttribute(): string
    {
        if (!$this->comment) return '';
        $clean = strip_tags($this->comment);
        return strlen($clean) > 120 ? substr($clean, 0, 120) . '...' : $clean;
    }

    // 🟢 Status textual
    public function getStatusLabelAttribute(): string
    {
        return $this->visible ? 'Visível' : 'Oculto';
    }

    /**
     * Scopes
     * ======================================
     */

    // 🔍 Apenas depoimentos visíveis
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Ordenar por nota (maior primeiro)
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('rating')->orderBy('client_name');
    }

    /**
     * Helpers
     * ======================================
     */

    // 🧾 Cria ou atualiza depoimento
    public static function upsertTestimonial(array $data): self
    {
        return self::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'client_name' => $data['client_name'],
            ],
            $data
        );
    }

    // 🔍 Busca depoimentos públicos de uma clínica
    public static function getVisibleForTenant(int $tenantId)
    {
        return self::ofTenant($tenantId)
            ->visible()
            ->ordered()
            ->get();
    }

    // 📦 Dados prontos para API pública
    public function toPublicArray(): array
    {
        return [
            'name' => $this->client_name,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'photo' => $this->photo_url,
            'stars' => $this->stars_html,
        ];
    }
}
