<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialCategory extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'type', 'color', 'active',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entries()
    {
        return $this->hasMany(FinancialEntry::class, 'category_id');
    }
}
