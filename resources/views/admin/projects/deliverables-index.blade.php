@extends('adminlte::page')

@section('title', 'Entregas — '.$project->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Entregas — {{ $project->title }}</h1>
        <a href="{{ route('admin.projects.kanban', $project) }}" class="btn btn-sm btn-secondary">Ver Kanban</a>
    </div>
@stop

@section('content')
    @livewire('deliverable.index', ['projectId' => $project->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
