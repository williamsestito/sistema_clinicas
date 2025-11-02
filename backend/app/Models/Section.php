<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin IdeHelperSection
 */
class Section extends Model
{
    use HasFactory;

    /**
     * Campos preenchíveis em massa.
     */
    protected $fillable = [
        'tenant_id',
        'slug',
        'title',
        'content',
        'image_url',
        'position',
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

    /**
     * Scopes
     * ======================================
     */

    // 🔍 Apenas seções ativas
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Filtrar por slug
    public function scopeSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // 🔍 Ordenar por posição
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Accessors e Helpers
     * ======================================
     */

    // 🖼️ Retorna URL completa da imagem
    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value
                ? (preg_match('/^https?:\/\//', $value)
                    ? $value
                    : asset('storage/' . ltrim($value, '/')))
                : asset('images/default-section.jpg')
        );
    }

    // 📛 Nome formatado para exibição
    public function getDisplayTitleAttribute(): string
    {
        return ucfirst($this->title ?? $this->slug);
    }

    // 🟢 Label de status
    public function getStatusLabelAttribute(): string
    {
        return $this->active ? 'Ativa' : 'Inativa';
    }

    // ✍️ Resumo do conteúdo
    public function getExcerptAttribute(): string
    {
        if (!$this->content) return '';
        return strlen($this->content) > 100
            ? substr(strip_tags($this->content), 0, 100) . '...'
            : strip_tags($this->content);
    }

    /**
     * Helpers
     * ======================================
     */

    // 🔄 Atualiza ou cria seção por slug
    public static function updateOrCreateBySlug(int $tenantId, string $slug, array $data)
    {
        return self::updateOrCreate(
            ['tenant_id' => $tenantId, 'slug' => $slug],
            $data
        );
    }

    // 🔍 Recupera seção única por slug
    public static function getBySlug(string $slug, int $tenantId): ?self
    {
        return self::ofTenant($tenantId)->slug($slug)->first();
    }
}
