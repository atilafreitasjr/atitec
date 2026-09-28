@extends('adminlte::page')

@section('title', $conversation->subject)

@section('content_header')<h1 class="mb-0">{{ $conversation->subject }}</h1>@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card title="{{ $conversation->client?->name ?? 'Comunicado geral' }}" theme="info">
        @foreach($conversation->messages as $m)
            <div class="border-bottom py-2">
                <p class="mb-1"><strong>{{ $m->user?->name }}</strong> <small class="text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</small></p>
                <p class="mb-0">{{ $m->body }}</p>
            </div>
        @endforeach
        @unless($conversation->closed_at)
        <form method="POST" action="{{ route('admin.conversations.update', $conversation) }}" class="mt-3">
            @csrf @method('PUT')
            <x-adminlte-textarea name="body" label="Responder" rows="3" required/>
            <x-adminlte-button label="Enviar resposta" theme="primary" type="submit"/>
        </form>
        <form method="POST" action="{{ route('admin.conversations.destroy', $conversation) }}" class="mt-2" onsubmit="return confirm('Encerrar conversa?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-secondary">Encerrar conversa</button>
        </form>
        @endunless
    </x-adminlte-card>
@stop
