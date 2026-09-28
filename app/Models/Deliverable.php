<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliverable extends Model
{
    public const STATUSES = [
        'planejamento' => 'Planejamento',
        'andamento' => 'Em andamento',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    protected $fillable = [
        'project_id', 'name', 'description', 'status',
        'start_date', 'end_date', 'notes', 'hours_estimated',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'hours_estimated' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(DeliverableTask::class);
    }

    public function pendingTasks(): HasMany
    {
        return $this->tasks()->where('status', 'pendente')->orderBy('order');
    }

    public function inProgressTasks(): HasMany
    {
        return $this->tasks()->where('status', 'andamento')->orderBy('order');
    }

    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', 'concluido')->orderBy('order');
    }
}
