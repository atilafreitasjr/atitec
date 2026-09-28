@extends('adminlte::page')

@section('title', ($client->exists ? 'Editar' : 'Novo').' cliente')

@section('content_header')<h1 class="mb-0">{{ $client->exists ? 'Editar cliente' : 'Novo cliente' }}</h1>@stop

@section('content')
    <x-adminlte-card>
        <form method="POST" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}">
            @csrf @if($client->exists)@method('PUT')@endif
            <div class="row">
                <div class="col-md-6"><x-adminlte-input name="name" label="Nome" :value="old('name', $client->name)" required/></div>
                <div class="col-md-6"><x-adminlte-input name="company" label="Empresa" :value="old('company', $client->company)"/></div>
            </div>
            <div class="row">
                <div class="col-md-4"><x-adminlte-input name="email" type="email" label="E-mail" :value="old('email', $client->email)" required/></div>
                <div class="col-md-4"><x-adminlte-input name="phone" label="Telefone" :value="old('phone', $client->phone)"/></div>
                <div class="col-md-4"><x-adminlte-input name="document" label="CPF/CNPJ" :value="old('document', $client->document)"/></div>
            </div>
            <x-adminlte-input name="address" label="Endereço" :value="old('address', $client->address)"/>
            <x-adminlte-textarea name="notes" label="Observações" rows="2">{{ old('notes', $client->notes) }}</x-adminlte-textarea>
            <div class="row">
                <div class="col-md-6"><x-adminlte-select name="user_id" label="Usuário vinculado (login do cliente)">
                    <option value="">—</option>
                    @foreach($users as $u)<option value="{{ $u->id }}" @selected(old('user_id', $client->user_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach
                </x-adminlte-select></div>
                <div class="col-md-6 d-flex align-items-end pb-3"><x-adminlte-input-switch name="active" label="Ativo" :checked="old('active', $client->active ?? true)"/></div>
            </div>
            <x-adminlte-button label="Salvar" theme="primary" type="submit"/>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-link">Cancelar</a>
        </form>
    </x-adminlte-card>
@stop
