<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',

        // Identidade profissional
        'specialty',
        'registration_type',
        'registration_number',

        // Perfil
        'bio',
        'about',
        'experience_years',
        'photo_url',

        // Formação e títulos
        'education',
        'specializations',

        // Localização
        'state',
        'city',
        'address',
        'number',
        'district',
        'complement',
        'zipcode',

        // Contatos
        'phone',
        'email_public',

        // Redes sociais
        'linkedin_url',
        'instagram_url',
        'website_url',

        // Configurações de agenda
        'default_start_hour',
        'default_end_hour',
        'default_consultation_time',

        // Flags
        'show_prices',
        'active',
    ];

    protected $casts = [
        'specialty' => 'array',         // múltiplas especialidades
        'specializations' => 'array',   // certificações, pós, cursos
        'education' => 'string',

        'default_consultation_time' => 'integer',
        'experience_years' => 'integer',

        'show_prices' => 'boolean',
        'active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function procedures()
    {
        return $this->hasMany(ProfessionalProcedure::class, 'professional_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'professional_id');
    }

    public function blocked()
    {
        return $this->hasMany(BlockedDate::class, 'professional_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'professional_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS (Helpers importantes)
    |--------------------------------------------------------------------------
    */

    public function getFullAddressAttribute()
    {
        return collect([
            $this->address,
            $this->number,
            $this->district,
            $this->city,
            $this->state,
            $this->zipcode,
        ])->filter()->join(', ');
    }

    public function getDisplayNameAttribute()
    {
        return $this->user?->name ?? 'Profissional';
    }

    public function getEmailAttribute()
    {
        return $this->email_public ?? $this->user?->email;
    }

    public function getProfileUrlAttribute()
    {
        return route('client.professional.show', $this->id);
    }

    public function getSpecialtyListAttribute()
    {
        return is_array($this->specialty)
            ? implode(', ', $this->specialty)
            : $this->specialty;
    }
}
