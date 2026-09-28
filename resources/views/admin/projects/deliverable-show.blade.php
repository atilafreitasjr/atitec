@extends('adminlte::page')

@section('title', $deliverable->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $deliverable->name }}</h1>
        <div class="d-flex gap-2">
            @can('deliverable.edit')
                <a href="{{ route('admin.projects.deliverables.edit', [$project, $deliverable]) }}" class="btn btn-sm btn-outline-primary">Editar entrega</a>
            @endcan
            <a href="{{ route('admin.projects.deliverables.index', $project) }}" class="btn btn-sm btn-secondary">Entregas</a>
        </div>
    </div>
@stop

@section('content')
    @livewire('deliverable.show', ['projectId' => $project->id, 'deliverableId' => $deliverable->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
