<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'status',
        'priority', 'assigned_to', 'due_date', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public const STATUSES = [
        'a_fazer' => 'A fazer',
        'fazendo' => 'Fazendo',
        'revisao' => 'Revisão',
        'concluido' => 'Concluído',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
