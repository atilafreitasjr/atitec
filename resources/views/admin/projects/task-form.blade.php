@extends('adminlte::page')

@section('title', ($taskId ? 'Editar' : 'Nova').' tarefa — '.$project->title)

@section('content_header')
    <h1 class="mb-0">{{ $taskId ? 'Editar tarefa' : 'Nova tarefa' }} — {{ $project->title }}</h1>
@stop

@section('content')
    @if($taskId)
        @livewire('task.edit', ['projectId' => $project->id, 'taskId' => $taskId])
    @else
        @livewire('task.create', ['projectId' => $project->id])
    @endif
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
