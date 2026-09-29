@extends('adminlte::page')

@section('title', 'Mensagens')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Mensagens</h1>
        @can('conversation.create')
            <a href="{{ route('admin.conversations.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nova conversa</a>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif

    @php
        $naoLidas = \App\Support\AdminNavbar::messageCount();
        $abertas = $conversations->whereNull('closed_at')->count();
        $gerais = $conversations->where('is_broadcast', true)->count();
    @endphp
    <div class="row">
        <div class="col-lg-4 col-6"><x-adminlte-info-box title="Mensagens não lidas" text="{{ $naoLidas }}" icon="bi bi-envelope-exclamation" theme="danger"/></div>
        <div class="col-lg-4 col-6"><x-adminlte-info-box title="Conversas abertas" text="{{ $abertas }}" icon="bi bi-chat-dots" theme="primary"/></div>
        <div class="col-lg-4 col-12"><x-adminlte-info-box title="Comunicados gerais" text="{{ $gerais }}" icon="bi bi-megaphone" theme="info"/></div>
    </div>

    <x-adminlte-card>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Assunto</th><th>Cliente</th><th>Mensagens</th><th>Status</th><th>Atualizada</th></tr></thead>
                <tbody>
                    @forelse($conversations as $c)
                        @php $naoLidasConversa = $c->messages->whereNull('read_at')->where('user_id', '!=', auth()->id())->count(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.conversations.show', $c) }}">{{ $c->subject }}</a>
                                @if($naoLidasConversa > 0)<span class="badge bg-danger ms-1">{{ $naoLidasConversa }} nova(s)</span>@endif
                            </td>
                            <td>{{ $c->client?->name ?? '—' }}</td>
                            <td>{{ $c->messages->count() }}</td>
                            <td>
                                @if($c->is_broadcast)<span class="badge bg-info">geral</span>@endif
                                @if($c->closed_at)<span class="badge bg-secondary">encerrada</span>@else<span class="badge bg-success">aberta</span>@endif
                            </td>
                            <td class="small text-muted">{{ $c->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhuma conversa ainda. Crie a primeira em “Nova conversa”.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $conversations->links() }}</div>
    </x-adminlte-card>
@stop
