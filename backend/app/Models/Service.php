<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @mixin IdeHelperService
 */
class Service extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'professional_id',
        'name',
        'description',
        'duration_min',
        'price',
        'active',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'duration_min' => 'integer',
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

    // 🔹 Profissional responsável
    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    // 🔹 Agendamentos vinculados
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Scopes para consultas dinâmicas
     * ======================================
     */

    // 🔍 Apenas serviços ativos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // 🔍 Filtrar por tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Filtrar por profissional
    public function scopeOfProfessional($query, int $professionalId)
    {
        return $query->where('professional_id', $professionalId);
    }

    // 🔍 Ordenar por nome
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Accessors e atributos virtuais
     * ======================================
     */

    // 💰 Formatar o preço
    public function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => 'R$ ' . number_format($attributes['price'] ?? 0, 2, ',', '.')
        );
    }

    // ⏱️ Duração formatada
    public function getDurationLabelAttribute(): string
    {
        $minutes = $this->duration_min;
        if ($minutes < 60) return "{$minutes} min";
        $hours = floor($minutes / 60);
        $rest = $minutes % 60;
        return $rest > 0 ? "{$hours}h {$rest}min" : "{$hours}h";
    }

    // 🟢 Label de status
    public function getStatusLabelAttribute(): string
    {
        return $this->active ? 'Ativo' : 'Inativo';
    }

    // 🧠 Descrição resumida (para cards ou listagens)
    public function getExcerptAttribute(): string
    {
        if (!$this->description) return '';
        return strlen($this->description) > 100
            ? substr(strip_tags($this->description), 0, 100) . '...'
            : strip_tags($this->description);
    }

    /**
     * Helpers
     * ======================================
     */

    // 🧾 Cria ou atualiza um serviço de forma prática
    public static function upsertService(array $data): self
    {
        return self::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'],
                'professional_id' => $data['professional_id'] ?? null,
            ],
            $data
        );
    }

    // 🔍 Buscar serviços com profissional
    public static function withProfessional(int $tenantId)
    {
        return self::ofTenant($tenantId)
            ->with('professional')
            ->ordered()
            ->get();
    }
}
