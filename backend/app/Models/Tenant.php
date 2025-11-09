<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Tenant extends Model
{
    use HasFactory;

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

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔗 RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */
    public function owner()         { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function users()         { return $this->hasMany(User::class); }
    public function clients()       { return $this->hasMany(Client::class); }
    public function professionals() { return $this->hasMany(Professional::class); }
    public function services()      { return $this->hasMany(Service::class); }
    public function appointments()  { return $this->hasMany(Appointment::class); }
    public function siteSettings()  { return $this->hasOne(SiteSetting::class); }

    /*
    |--------------------------------------------------------------------------
    | 🧠 ACCESSORS E COMPUTED FIELDS
    |--------------------------------------------------------------------------
    */
    public function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
                $value
                    ? (preg_match('/^https?:\/\//', $value)
                        ? $value
                        : Storage::disk('public')->url($value))
                    : asset('images/default-logo.png')
        );
    }

    public function getPrimaryColorAttribute($value): string
    {
        return $value ?: '#004d40';
    }

    public function getSecondaryColorAttribute($value): string
    {
        return $value ?: '#009688';
    }

    public function getDisplayNameAttribute(): string
    {
        return ucfirst($this->name);
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at?->format('d/m/Y H:i') ?? '-';
    }

    /*
    |--------------------------------------------------------------------------
    | 🔍 SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('name', 'like', "%{$term}%") : $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | ⚙️ HELPERS
    |--------------------------------------------------------------------------
    */
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

    public function updateSettings(array $data): self
    {
        $settings = $this->settings ?? [];
        $this->settings = array_merge($settings, $data);
        $this->save();
        return $this;
    }

    public function palette(): array
    {
        return [
            'primary'   => $this->primary_color,
            'secondary' => $this->secondary_color,
        ];
    }

    public static function findByDomain(string $host): ?self
    {
        return self::whereJsonContains('settings->domains', $host)->first();
    }
}
