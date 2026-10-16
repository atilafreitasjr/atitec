<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'project_type',
        'budget_range', 'details', 'status', 'source', 'client_id',
        'proposal_title', 'proposal_summary', 'proposal_amount', 'proposal_valid_until',
        'proposal_status', 'proposal_sent_at', 'proposal_responded_at', 'converted_project_id',
    ];

    protected function casts(): array
    {
        return [
            'proposal_amount' => 'decimal:2',
            'proposal_valid_until' => 'date',
            'proposal_sent_at' => 'datetime',
            'proposal_responded_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'novo' => 'Novo',
        'em_contato' => 'Em contato',
        'proposta' => 'Proposta',
        'fechado' => 'Fechado',
        'perdido' => 'Perdido',
    ];

    public const PROPOSAL_STATUSES = [
        'rascunho' => 'Rascunho',
        'enviada' => 'Enviada',
        'aceita' => 'Aceita',
        'recusada' => 'Recusada',
        'convertida' => 'Convertida em projeto',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function convertedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'converted_project_id');
    }

    public function canRespondToProposal(): bool
    {
        return $this->proposal_status === 'enviada'
            && ($this->proposal_valid_until === null || $this->proposal_valid_until->isToday() || $this->proposal_valid_until->isFuture());
    }
}
