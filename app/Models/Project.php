<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'client_id', 'title', 'slug', 'description', 'problem',
        'solution', 'results', 'url', 'segment', 'technologies',
        'status', 'progress', 'deadline', 'budget', 'image',
        'featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'budget' => 'decimal:2',
            'featured' => 'boolean',
        ];
    }

    public const STATUSES = [
        'prospeccao' => 'Prospecção',
        'em_desenvolvimento' => 'Em desenvolvimento',
        'homologacao' => 'Homologação',
        'entregue' => 'Entregue',
        'suporte' => 'Suporte',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class)->orderBy('start_date')->orderBy('created_at');
    }

    public function deliverableTasks(): HasMany
    {
        return $this->hasMany(DeliverableTask::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
