@extends('adminlte::page')

@section('title', $conversation->subject)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $conversation->subject }}</h1>
        <a href="{{ route('admin.conversations.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    <x-adminlte-card>
        <x-slot name="title">
            <i class="bi bi-chat-dots me-1"></i>
            {{ $conversation->client?->name ?? 'Comunicado geral' }}
            @if($conversation->is_broadcast)<span class="badge bg-info ms-2">geral</span>@endif
            @if($conversation->closed_at)<span class="badge bg-secondary ms-1">encerrada</span>@endif
        </x-slot>

        <div style="max-height:52vh;overflow-y:auto">
            @forelse($conversation->messages as $m)
                @php $mine = $m->user_id === auth()->id(); @endphp
                <div class="d-flex mb-3 {{ $mine ? 'justify-content-end' : '' }}">
                    <div class="p-3 rounded-3 {{ $mine ? 'bg-primary text-white' : 'bg-body-secondary' }}" style="max-width:75%">
                        <p class="mb-1 small fw-bold">
                            {{ $mine ? 'Você' : ($m->user?->name ?? 'Cliente') }}
                            <span class="fw-normal opacity-75">· {{ $m->created_at->format('d/m/Y H:i') }}</span>
                        </p>
                        <p class="mb-0" style="white-space:pre-wrap">{{ $m->body }}</p>
                    </div>
                </div>
            @empty
                <p class="text-muted">Nenhuma mensagem.</p>
            @endforelse
        </div>

        <hr>

        @if($conversation->closed_at)
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Conversa encerrada em {{ $conversation->closed_at->format('d/m/Y H:i') }}.</span>
                <form method="POST" action="{{ route('admin.conversations.reopen', $conversation) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">Reabrir conversa</button>
                </form>
            </div>
        @else
            @can('conversation.edit')
                <form method="POST" action="{{ route('admin.conversations.update', $conversation) }}">
                    @csrf @method('PUT')
                    <x-adminlte-textarea name="body" label="Responder" rows="3" placeholder="Escreva a resposta..." required/>
                    <x-adminlte-button label="Enviar resposta" theme="primary" type="submit" icon="bi bi-send"/>
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="if(confirm('Encerrar conversa?')) document.getElementById('form-encerrar').submit()">Encerrar</button>
                </form>
                <form id="form-encerrar" method="POST" action="{{ route('admin.conversations.destroy', $conversation) }}" class="d-none">
                    @csrf @method('DELETE')
                </form>
            @endcan
        @endif
    </x-adminlte-card>
@stop
