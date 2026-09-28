@extends('adminlte::page')

@section('title', 'Tarefas — '.$project->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Tarefas — {{ $project->title }}</h1>
        <div class="d-flex gap-2">
            @can('task.create')
                <a href="{{ route('admin.projects.tasks.create', $project) }}" class="btn btn-sm btn-primary">Nova tarefa</a>
            @endcan
            <a href="{{ route('admin.projects.kanban', $project) }}" class="btn btn-sm btn-secondary">Ver Kanban</a>
        </div>
    </div>
@stop

@section('content')
    @livewire('task.index', ['projectId' => $project->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
