@extends('adminlte::page')

@section('title', 'Central de ajuda')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p>
            <h1 class="mb-0">Central de ajuda</h1>
            <p class="text-muted mb-0">Como funciona o seu workspace, do cadastro ao financeiro.</p>
        </div>
        <span class="badge text-bg-warning fs-6">Guia do Cliente</span>
    </div>
@stop

@section('content')
    <div class="row g-3">
        <div class="col-lg-3">
            <x-adminlte-card title="Tópicos do guia" icon="bi bi-book" theme="primary">
                <div class="list-group list-group-flush">
                    @foreach ($nav as $slug => $label)
                        <a href="{{ $slug === 'README' ? route('cliente.ajuda') : route('cliente.ajuda', ['page' => $slug]) }}"
                           class="list-group-item list-group-item-action px-0 {{ $current === $slug ? 'fw-bold text-warning' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <hr>
                <a href="{{ route('cliente.mensagens') }}" class="small fw-semibold">Não achou o que precisava? Fale com a ATITEC →</a>
            </x-adminlte-card>
        </div>
        <div class="col-lg-9">
            <x-adminlte-card>
                <article class="adminlte-docs">{!! $html !!}</article>
            </x-adminlte-card>
        </div>
    </div>
@stop

@include('docs.styles')
