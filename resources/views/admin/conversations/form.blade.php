@extends('adminlte::page')

@section('title', 'Nova conversa')

@section('content_header')<h1 class="mb-0">Nova conversa</h1>@stop

@section('content')
    <x-adminlte-card>
        <form method="POST" action="{{ route('admin.conversations.store') }}">
            @csrf
            <x-adminlte-input name="subject" label="Assunto" :value="old('subject')" required/>
            <div class="row">
                <div class="col-md-8"><x-adminlte-select name="client_id" label="Cliente (vazio = comunicado geral)">
                    <option value="">— comunicado geral —</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }}</option>@endforeach
                </x-adminlte-select></div>
                <div class="col-md-4 d-flex align-items-end pb-3"><x-adminlte-input-switch name="is_broadcast" label="Comunicado geral"/></div>
            </div>
            <x-adminlte-textarea name="body" label="Mensagem" rows="4" required>{{ old('body') }}</x-adminlte-textarea>
            <x-adminlte-button label="Enviar" theme="primary" type="submit"/>
        </form>
    </x-adminlte-card>
@stop
