<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperNotificationLog
 */
class NotificationLog extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'tenant_id',
        'appointment_id',
        'channel',
        'type',
        'recipient',
        'template',
        'sent_at',
        'status',
        'error_message',
    ];

    /**
     * Conversões automáticas de tipo.
     */
    protected $casts = [
        'sent_at' => 'datetime',
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

    // 🔹 Agendamento relacionado
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Scopes e Helpers
     * ======================================
     */

    // 🔍 Filtrar por Tenant
    public function scopeOfTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // 🔍 Notificações enviadas com sucesso
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    // 🔍 Notificações com falha
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // 🔍 Filtro por canal (email / whatsapp)
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    // 🔍 Filtro por tipo (new, reminder_24h, etc.)
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Helpers e Accessors
     * ======================================
     */

    // 🕒 Data formatada
    public function getSentAtFormattedAttribute(): string
    {
        return $this->sent_at ? $this->sent_at->format('d/m/Y H:i') : '-';
    }

    // 💬 Nome legível do canal
    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'email' => 'E-mail',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($this->channel ?? '-'),
        };
    }

    // 🔔 Tipo de notificação formatado
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'new' => 'Nova marcação',
            'reminder_24h' => 'Lembrete 24h antes',
            'reminder_2h' => 'Lembrete 2h antes',
            'status_update' => 'Atualização de status',
            default => ucfirst($this->type ?? '-'),
        };
    }

    // ✅ Status legível
    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'success'
            ? 'Enviado com sucesso'
            : 'Falha no envio';
    }

    // ⚠️ Verifica se houve erro
    public function getHasErrorAttribute(): bool
    {
        return $this->status === 'failed' && !empty($this->error_message);
    }
}
