@extends('adminlte::page')

@section('title', 'Canais de comunicação')

@section('content_header')<h1 class="mb-0">Canais de comunicação</h1>@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <div class="row">
        <div class="col-md-7"><x-adminlte-card title="Canais (alimentam o site)" theme="primary">
            @foreach($channels as $c)
            <form method="POST" action="{{ route('admin.channels.update', $c) }}" class="border-bottom py-2">
                @csrf @method('PUT')
                <div class="row g-2">
                    <div class="col-md-3"><input name="label" value="{{ $c->label }}" class="form-control" required></div>
                    <div class="col-md-3"><input name="value" value="{{ $c->value }}" class="form-control" required></div>
                    <div class="col-md-4"><input name="url" value="{{ $c->url }}" class="form-control" placeholder="URL"></div>
                    <div class="col-md-2 d-flex gap-1">
                        <input type="hidden" name="type" value="{{ $c->type }}">
                        <input type="hidden" name="sort_order" value="{{ $c->sort_order }}">
                        <button class="btn btn-sm btn-primary">Salvar</button>
                    </div>
                </div>
            </form>
            @endforeach
        </x-adminlte-card></div>
        <div class="col-md-5"><x-adminlte-card title="Novo canal" theme="success">
            <form method="POST" action="{{ route('admin.channels.store') }}">
                @csrf
                <x-adminlte-input name="type" label="Tipo (ex: linkedin)" required/>
                <x-adminlte-input name="label" label="Rótulo" required/>
                <x-adminlte-input name="value" label="Valor" required/>
                <x-adminlte-input name="url" label="URL"/>
                <x-adminlte-input name="sort_order" type="number" label="Ordem" value="10" required/>
                <x-adminlte-button label="Adicionar" theme="success" type="submit"/>
            </form>
        </x-adminlte-card></div>
    </div>
@stop
