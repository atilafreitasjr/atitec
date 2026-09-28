@extends('adminlte::page')

@section('title', 'Mensagens')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Mensagens</h1>
        <a href="{{ route('admin.conversations.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nova conversa</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Assunto</th><th>Cliente</th><th>Mensagens</th><th>Atualizada</th></tr></thead>
            <tbody>@forelse($conversations as $c)<tr>
                <td><a href="{{ route('admin.conversations.show', $c) }}">{{ $c->subject }}</a> @if($c->is_broadcast)<span class="badge bg-info">geral</span>@endif @if($c->closed_at)<span class="badge bg-secondary">encerrada</span>@endif</td>
                <td>{{ $c->client?->name ?? '—' }}</td><td>{{ $c->messages->count() }}</td><td>{{ $c->updated_at->format('d/m/Y H:i') }}</td>
            </tr>@empty<tr><td colspan="4" class="text-center">Nenhuma conversa.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $conversations->links() }}</div>
    </x-adminlte-card>
@stop
