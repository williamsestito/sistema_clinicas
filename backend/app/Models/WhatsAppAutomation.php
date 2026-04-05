<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppAutomation extends Model
{
    protected $table = 'whatsapp_automations';

    protected $fillable = [
        'tenant_id',
        'send_confirmation',
        'send_reminder',
        'reminder_hours_before',
        'send_cancellation',
        'send_reschedule',
        'confirmation_template',
        'reminder_template',
        'cancellation_template',
        'reschedule_template',
        'active',
    ];

    protected $casts = [
        'send_confirmation' => 'boolean',
        'send_reminder'     => 'boolean',
        'send_cancellation' => 'boolean',
        'send_reschedule'   => 'boolean',
        'active'            => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
