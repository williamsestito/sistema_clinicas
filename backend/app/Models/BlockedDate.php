<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'professional_id',
        'date',
        'reason'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
