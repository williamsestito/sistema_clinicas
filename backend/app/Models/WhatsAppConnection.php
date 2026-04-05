<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConnection extends Model
{
    protected $table = 'whatsapp_connections';

    protected $fillable = [
        'tenant_id',
        'provider',
        'phone_number_id',
        'business_account_id',
        'api_token',
        'webhook_verify_token',
        'active',
        'status',
        'connected_at',
        'settings',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'connected_at' => 'datetime',
        'settings'     => 'array',
    ];

    protected $hidden = [
        'api_token',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
