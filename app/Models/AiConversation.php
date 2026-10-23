<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiConversation extends Model
{
    protected $fillable = [
        'token', 'visitor_name', 'visitor_email', 'visitor_phone',
        'status', 'category', 'urgency', 'summary', 'ip_address',
        'handled_by', 'escalated_at', 'handled_at', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'escalated_at' => 'datetime',
            'handled_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'bot' => 'Em conversa com a IA',
        'encaminhado' => 'Aguardando atendimento',
        'em_atendimento' => 'Em atendimento',
        'respondido' => 'Respondido',
        'encerrado' => 'Encerrado',
    ];

    public const URGENCIES = [
        'baixa' => 'Baixa',
        'normal' => 'Normal',
        'alta' => 'Alta',
    ];

    public const CATEGORIES = [
        'comercial' => 'Comercial',
        'tecnico' => 'Técnico',
        'financeiro' => 'Financeiro',
        'suporte' => 'Suporte',
        'outro' => 'Outro',
    ];

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getProtocolAttribute(): string
    {
        return 'IA-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function hasContact(): bool
    {
        return filled($this->visitor_email) || filled($this->visitor_phone);
    }

    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->visitor_phone);

        if ($digits === '') {
            return null;
        }

        // Números brasileiros precisam do DDI 55; números já internacionais são mantidos.
        if (! str_starts_with($digits, '55') || strlen($digits) < 12) {
            $digits = '55'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    public function isEscalated(): bool
    {
        return $this->status !== 'bot';
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['encaminhado', 'em_atendimento']);
    }
}
