@extends('adminlte::page')

@section('title', $project->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $project->title }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.projects.kanban', $project) }}" class="btn btn-success">Kanban</a>
            <a href="{{ route('admin.projects.deliverables.index', $project) }}" class="btn btn-outline-primary">Entregas</a>
            <a href="{{ route('admin.projects.tasks.index', $project) }}" class="btn btn-outline-primary">Tarefas</a>
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-outline-secondary">Editar</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <x-adminlte-card title="Dados" theme="primary" icon="bi bi-info-circle">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8">{{ $project->client?->name ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ $project->status }} ({{ $project->progress }}%)</dd>
                    <dt class="col-sm-4">URL</dt><dd class="col-sm-8">@if($project->url)<a href="{{ $project->url }}" target="_blank">{{ $project->url }}</a>@else — @endif</dd>
                    <dt class="col-sm-4">Segmento</dt><dd class="col-sm-8">{{ $project->segment ?? '—' }}</dd>
                    <dt class="col-sm-4">Prazo</dt><dd class="col-sm-8">{{ $project->deadline?->format('d/m/Y') ?? '—' }}</dd>
                </dl>
                <p class="mt-3 mb-0">{{ $project->description }}</p>
            </x-adminlte-card>
        </div>
        <div class="col-md-3">
            <x-adminlte-card title="Tarefas ({{ $project->tasks->count() }})" theme="info" icon="bi bi-list-task">
                <ul class="list-group list-group-flush">
                    @forelse($project->tasks as $t)
                        <li class="list-group-item">{{ $t->title }} <span class="badge bg-secondary float-end">{{ $t->status }}</span></li>
                    @empty<li class="list-group-item">Nenhuma tarefa.</li>@endforelse
                </ul>
            </x-adminlte-card>
        </div>
        <div class="col-md-3">
            <x-adminlte-card title="Faturas" theme="warning" icon="bi bi-receipt">
                <ul class="list-group list-group-flush">
                    @forelse($project->invoices as $f)
                        <li class="list-group-item d-flex justify-content-between"><span>{{ $f->title }}</span><span class="badge bg-secondary">{{ $f->status }}</span></li>
                    @empty<li class="list-group-item">Nenhuma fatura.</li>@endforelse
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop
