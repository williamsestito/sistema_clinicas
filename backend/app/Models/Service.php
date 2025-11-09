<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'name',
        'description',
        'duration_min',
        'price',
        'active'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
