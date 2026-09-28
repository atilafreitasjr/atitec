@extends('adminlte::page')

@section('title', 'Kanban — '.$project->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Kanban — {{ $project->title }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.projects.deliverables.index', $project) }}" class="btn btn-sm btn-outline-primary">Entregas</a>
            <a href="{{ route('admin.projects.tasks.index', $project) }}" class="btn btn-sm btn-outline-primary">Tarefas</a>
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-secondary">Voltar ao projeto</a>
        </div>
    </div>
@stop

@section('content')
    @livewire('kanban.board', ['projectId' => $project->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
