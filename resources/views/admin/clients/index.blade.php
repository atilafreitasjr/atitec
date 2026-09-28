@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Clientes</h1>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Novo cliente</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Nome</th><th>Empresa</th><th>E-mail</th><th>Projetos</th><th>Faturas</th><th></th></tr></thead>
            <tbody>@forelse($clients as $c)<tr>
                <td><a href="{{ route('admin.clients.show', $c) }}">{{ $c->name }}</a></td>
                <td>{{ $c->company ?? '—' }}</td><td>{{ $c->email }}</td>
                <td>{{ $c->projects_count }}</td><td>{{ $c->invoices_count }}</td>
                <td class="text-end"><a href="{{ route('admin.clients.edit', $c) }}" class="btn btn-sm btn-outline-primary">Editar</a></td>
            </tr>@empty<tr><td colspan="6" class="text-center">Nenhum cliente.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $clients->links() }}</div>
    </x-adminlte-card>
@stop
