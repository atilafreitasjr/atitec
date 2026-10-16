@extends('adminlte::page')

@section('title', 'Kanban — '.$project->title)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <a href="{{ route('cliente.projeto', $project->id) }}" class="small">← Voltar ao projeto</a>
            <h1 class="mb-0">Acompanhamento — {{ $project->title }}</h1>
        </div>
        <span class="badge text-bg-warning fs-6">Somente leitura</span>
    </div>
@stop

@section('content')
    @livewire('kanban.board', ['projectId' => $project->id, 'readonly' => true])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
