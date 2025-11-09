<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'name',
        'price',
        'duration_min',
        'description',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function tenant()      { return $this->belongsTo(Tenant::class); }
    public function professional(){ return $this->belongsTo(Professional::class); }
}
