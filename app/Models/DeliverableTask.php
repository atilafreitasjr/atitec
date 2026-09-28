<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliverableTask extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'pendente' => 'Pendente',
        'andamento' => 'Em andamento',
        'concluido' => 'Concluído',
    ];

    public const PRIORITIES = ['baixa', 'media', 'alta'];

    protected $fillable = [
        'project_id', 'deliverable_id', 'phase', 'user_id', 'title',
        'description', 'status', 'priority', 'due_date', 'start_date',
        'end_date', 'hours_estimated', 'hours_actual', 'hourly_rate',
        'order', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'hourly_rate' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Garante o isolamento por projeto: project_id sempre espelha a entrega.
        static::saving(function (DeliverableTask $task) {
            if ($task->deliverable_id && ! $task->project_id) {
                $task->project_id = Deliverable::find($task->deliverable_id)?->project_id;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function deliverable(): BelongsTo
    {
        return $this->belongsTo(Deliverable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCostEstimatedAttribute(): ?float
    {
        if ($this->hours_estimated && $this->hourly_rate) {
            return (float) $this->hours_estimated * (float) $this->hourly_rate;
        }

        return null;
    }

    public function getCostActualAttribute(): ?float
    {
        if ($this->hours_actual && $this->hourly_rate) {
            return (float) $this->hours_actual * (float) $this->hourly_rate;
        }

        return null;
    }
}
