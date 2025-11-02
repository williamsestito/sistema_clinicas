<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBanner
 */
class Banner extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'title',
        'subtitle',
        'image_url',
        'link_url',
        'position',
        'active',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'active' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Relações
     * ======================================
     */

    // 🔹 Clínica (Tenant)
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Apenas banners ativos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Ordenar por posição
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc');
    }

    // 🔍 Filtrar por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Helper — Retorna a URL completa da imagem
     */
    public function getImageFullUrlAttribute(): string
    {
        if (!$this->image_url) {
            return asset('images/default-banner.jpg');
        }

        // Se já for uma URL absoluta (ex: CDN)
        if (preg_match('/^https?:\/\//', $this->image_url)) {
            return $this->image_url;
        }

        // Caso contrário, gera a URL do storage
        return asset('storage/' . ltrim($this->image_url, '/'));
    }

    /**
     * Helper — Retorna o título formatado
     */
    public function getFormattedTitleAttribute(): string
    {
        return ucfirst($this->title ?? '');
    }

    /**
     * Helper — Status do banner (ativo/inativo)
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }
}
