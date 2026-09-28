@extends('adminlte::page')

@section('title', ($deliverableId ? 'Editar' : 'Nova').' entrega — '.$project->title)

@section('content_header')
    <h1 class="mb-0">{{ $deliverableId ? 'Editar entrega' : 'Nova entrega' }} — {{ $project->title }}</h1>
@stop

@section('content')
    @if($deliverableId)
        @livewire('deliverable.edit', ['projectId' => $project->id, 'deliverableId' => $deliverableId])
    @else
        @livewire('deliverable.create', ['projectId' => $project->id])
    @endif
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop
