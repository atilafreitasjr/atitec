@extends('adminlte::page')

@section('title', $title.' — Guia do Cliente')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Guia do Cliente</p>
            <h1 class="mb-0">{{ $title }}</h1>
        </div>
        <span class="badge text-bg-secondary align-self-start">Documentação do sistema</span>
    </div>
@stop

@section('content')
    <div class="row g-3">
        <div class="col-lg-3">
            <x-adminlte-card title="Tópicos do guia" icon="bi bi-book" theme="primary">
                <div class="list-group list-group-flush">
                    @foreach ($nav as $slug => $label)
                        <a href="{{ $slug === 'README' ? url('docs') : url('docs/'.$slug) }}"
                           class="list-group-item list-group-item-action px-0 {{ $current === $slug ? 'fw-bold text-warning' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
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
