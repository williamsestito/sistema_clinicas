<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'social_name',
        'use_social_name',
        'email',
        'phone',
        'document',
        'rg',
        'birthdate',
        'gender',
        'civil_status',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'consent_marketing',
        'notes',
        'active', // ✅ IMPORTANTE: habilita edição do status ativo/inativo
    ];

    protected $casts = [
        'birthdate' => 'date',
        'consent_marketing' => 'boolean',
        'use_social_name' => 'boolean',
        'active' => 'boolean',
    ];

    // 🔗 Relações
    public function tenant()        { return $this->belongsTo(Tenant::class); }
    public function user()          { return $this->belongsTo(User::class); }
    public function appointments()  { return $this->hasMany(Appointment::class); }

    // 🔍 Scopes
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeMarketingOptIn($query)
    {
        return $query->where('consent_marketing', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    // 🎨 Helpers
    public function getAgeAttribute(): ?int
    {
        return $this->birthdate ? $this->birthdate->age : null;
    }

    public function getFormattedPhoneAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->phone ?? '');
        if (!$digits) return '-';

        return match (strlen($digits)) {
            11 => sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 5), substr($digits, 7)),
            10 => sprintf('(%s) %s-%s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6)),
            default => $this->phone,
        };
    }

    public function getMarketingStatusLabelAttribute(): string
    {
        return $this->consent_marketing ? 'Aceitou comunicações' : 'Não aceitou';
    }
}
