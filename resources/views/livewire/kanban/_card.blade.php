@php
    $readonly = $readonly ?? false;
    $isConcluido = $column === 'concluido';
    $borderClass = match($column) {
        'andamento' => 'border-info',
        'concluido' => 'border-success',
        default => '',
    };
    $priorityClass = match($task->priority) {
        'alta' => 'bg-danger',
        'media' => 'bg-info',
        default => 'bg-secondary',
    };
@endphp

<div class="card mb-2 {{ $borderClass }} {{ $isConcluido ? 'opacity-75' : '' }}" x-data="{ expanded: false }">
    <div class="card-body p-2" @unless($readonly) style="cursor:pointer" wire:click="openTaskModal({{ $task->id }})" @endunless>
        <div class="d-flex justify-content-between gap-1">
            <h6 class="card-title small mb-1 {{ $isConcluido ? 'text-decoration-line-through text-muted' : '' }}">{{ $task->title }}</h6>

            @unless($readonly)
            <div class="d-flex flex-column gap-0" x-on:click.stop>
                @if($column !== 'concluido')
                    <button wire:click="moveTask({{ $task->id }}, 'up')" class="btn btn-sm btn-link text-muted p-0" title="Mover para próxima coluna">▲</button>
                @endif
                @if($column !== 'pendente')
                    <button wire:click="moveTask({{ $task->id }}, 'down')" class="btn btn-sm btn-link text-muted p-0" title="Mover para coluna anterior">▼</button>
                @endif
                <button x-on:click.stop="if(confirm('Remover tarefa?')) { $wire.deleteTask({{ $task->id }}); }" class="btn btn-sm btn-link text-danger p-0" title="Excluir">✕</button>
            </div>
            @endunless
        </div>

        @if($task->description)
            <p class="card-text small text-muted mb-1">{{ \Illuminate\Support\Str::limit(strip_tags($task->description), 120) }}</p>
        @endif

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
            <div class="d-flex align-items-center gap-1">
                @if($task->user)
                    <span class="badge bg-primary" title="Responsável: {{ $task->user->name }}">{{ \Illuminate\Support\Str::limit($task->user->name, 16) }}</span>
                @endif
                <span class="badge {{ $priorityClass }}">{{ ucfirst($task->priority) }}</span>
                @if($task->due_date)
                    <small class="{{ $task->due_date->isPast() && ! $isConcluido ? 'text-danger fw-bold' : 'text-muted' }}">{{ $task->due_date->format('d/m/Y') }}</small>
                @endif
            </div>
            @if(empty($selectedDeliverable) && $task->deliverable)
                <span class="badge bg-light text-dark border">{{ \Illuminate\Support\Str::limit($task->deliverable->name, 15) }}</span>
            @endif
        </div>
        @if($task->tags)
            <div class="mt-1">
                @foreach(explode(',', $task->tags) as $tag)
                    <span class="badge bg-light text-muted border fw-normal">{{ trim($tag) }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card-footer p-1 text-center">
        <button x-on:click.stop="expanded = !expanded" class="btn btn-sm btn-link text-muted py-0 small">
            <span x-text="expanded ? 'Ocultar detalhes' : 'Ver detalhes'"></span>
        </button>
        <div x-show="expanded" class="text-start small text-muted px-2 pb-2">
            @if($task->deliverable)<div class="d-flex justify-content-between"><span>Entrega:</span><strong>{{ $task->deliverable->name }}</strong></div>@endif
            @if($task->phase)<div class="d-flex justify-content-between"><span>Fase:</span><strong>{{ $task->phase }}</strong></div>@endif
            @if($task->hours_estimated)<div class="d-flex justify-content-between"><span>Horas previstas:</span><strong>{{ number_format($task->hours_estimated, 1, ',', '.') }}h</strong></div>@endif
            @if($task->hours_actual)<div class="d-flex justify-content-between"><span>Horas realizadas:</span><strong>{{ number_format($task->hours_actual, 1, ',', '.') }}h</strong></div>@endif
            @if($task->cost_estimated)<div class="d-flex justify-content-between"><span>Custo estimado:</span><strong>R$ {{ number_format($task->cost_estimated, 2, ',', '.') }}</strong></div>@endif
            @if($task->cost_actual)<div class="d-flex justify-content-between"><span>Custo real:</span><strong>R$ {{ number_format($task->cost_actual, 2, ',', '.') }}</strong></div>@endif
            @if($task->start_date)<div class="d-flex justify-content-between"><span>Início:</span><strong>{{ $task->start_date->format('d/m/Y') }}</strong></div>@endif
            @if($task->end_date)<div class="d-flex justify-content-between"><span>Término:</span><strong>{{ $task->end_date->format('d/m/Y') }}</strong></div>@endif
            @if($task->user)<div class="d-flex justify-content-between"><span>Responsável:</span><strong>{{ $task->user->name }}</strong></div>@endif
        </div>
    </div>
</div>
