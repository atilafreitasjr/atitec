@extends('adminlte::page')

@section('title', ($user->exists ? 'Editar' : 'Novo').' usuário')

@section('content_header')
    <h1 class="mb-0">{{ $user->exists ? 'Editar usuário' : 'Novo usuário' }}</h1>
@stop

@section('content')
    @if($errors->any())<x-adminlte-alert theme="danger" title="Verifique os campos">{{ $errors->first() }}</x-adminlte-alert>@endif

    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card>
                <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
                    @csrf @if($user->exists)@method('PUT')@endif

                    <div class="row">
                        <div class="col-md-6"><x-adminlte-input name="name" label="Nome" :value="old('name', $user->name)" required/></div>
                        <div class="col-md-6"><x-adminlte-input name="email" type="email" label="E-mail" :value="old('email', $user->email)" required/></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <x-adminlte-input name="password" type="password" label="Senha {{ $user->exists ? '(deixe vazio para manter)' : '' }}" autocomplete="new-password"/>
                        </div>
                        <div class="col-md-6"><x-adminlte-input name="password_confirmation" type="password" label="Confirmar senha" autocomplete="new-password"/></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <x-adminlte-select name="role" label="Papel">
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ $role }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-select name="client_id" label="Cliente vinculado (para acesso ao portal)">
                                <option value="">— nenhum —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $user->client_id) == $client->id)>{{ $client->name }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                    </div>

                    <x-adminlte-input-switch name="active" label="Ativo (pode fazer login)" :checked="old('active', $user->active ?? true)"/>

                    <div class="mt-3">
                        <x-adminlte-button label="Salvar" theme="primary" type="submit"/>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-link">Cancelar</a>
                    </div>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-md-4">
            <x-adminlte-card title="Como funciona" theme="info" icon="bi bi-info-circle">
                <ul class="small mb-0 ps-3">
                    <li>O <strong>papel</strong> define as permissões (gerenciadas em “Papéis e permissões”).</li>
                    <li>Vincule um <strong>cliente</strong> para que usuários do papel <em>cliente</em> vejam apenas os projetos dele no portal.</li>
                    <li>Usuários <strong>inativos</strong> não conseguem fazer login.</li>
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop
