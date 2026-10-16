<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'project_id', 'title', 'description', 'amount',
        'discount_amount', 'discount_reason',
        'due_date', 'paid_at', 'status', 'type', 'gateway',
        'external_id', 'pdf_path', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function billedTasks(): HasMany
    {
        return $this->hasMany(DeliverableTask::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /** Desconto/cortesia aplicado pela ATITEC (diferença assumida pela empresa). */
    public function discountAmount(): float
    {
        return (float) ($this->discount_amount ?? 0);
    }

    public function balance(): float
    {
        return max(0, round((float) $this->amount - $this->discountAmount() - $this->paidAmount(), 2));
    }

    /** Total coberto (pagamentos + cortesia) atinge o valor da fatura. */
    public function isSettled(): bool
    {
        return round($this->paidAmount() + $this->discountAmount(), 2) >= (float) $this->amount;
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['pendente', 'vencido'], true) && $this->due_date->lt(today());
    }
}
