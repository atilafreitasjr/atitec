@extends('adminlte::page')

@section('title', 'Nova conversa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Nova conversa</h1>
        <a href="{{ route('admin.conversations.index') }}" class="btn btn-sm btn-secondary">Voltar</a>
    </div>
@stop

@section('content')
    @if($errors->any())<x-adminlte-alert theme="danger" title="Verifique os campos">{{ $errors->first() }}</x-adminlte-alert>@endif

    <div class="row">
        <div class="col-md-8">
            <x-adminlte-card title="Mensagem" theme="primary" icon="bi bi-chat-dots">
                <form method="POST" action="{{ route('admin.conversations.store') }}">
                    @csrf
                    <x-adminlte-input name="subject" label="Assunto" :value="old('subject')" placeholder="ex.: Ajustes na entrega da semana" required/>

                    <div class="mb-3">
                        <label class="form-label d-block">Destino</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="destino" id="destino-cliente" value="cliente"
                                   @checked(old('destino', 'cliente') === 'cliente')>
                            <label class="form-check-label" for="destino-cliente">Um cliente específico</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="destino" id="destino-geral" value="geral"
                                   @checked(old('destino') === 'geral')>
                            <label class="form-check-label" for="destino-geral">Comunicado geral (todos os clientes)</label>
                        </div>
                    </div>

                    <div id="campo-cliente">
                        <x-adminlte-select name="client_id" label="Cliente" id="client_id">
                            <option value="">Selecione o cliente...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>

                    <x-adminlte-textarea name="body" label="Mensagem" rows="5" placeholder="Escreva a mensagem..." required>{{ old('body') }}</x-adminlte-textarea>

                    <x-adminlte-button label="Enviar mensagem" theme="primary" type="submit" icon="bi bi-send"/>
                    <a href="{{ route('admin.conversations.index') }}" class="btn btn-link">Cancelar</a>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-md-4">
            <x-adminlte-card title="Como funciona" theme="info" icon="bi bi-info-circle">
                <ul class="small mb-0 ps-3">
                    <li><strong>Um cliente específico:</strong> conversa privada, aparece no portal dele.</li>
                    <li><strong>Comunicado geral:</strong> aparece para todos os clientes no portal.</li>
                    <li>Abra a conversa para marcar as mensagens do cliente como lidas.</li>
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
<script>
    (function () {
        const cliente = document.getElementById('destino-cliente');
        const geral = document.getElementById('destino-geral');
        const campo = document.getElementById('campo-cliente');
        const select = document.getElementById('client_id');

        function sync() {
            const isGeral = geral && geral.checked;
            if (campo) campo.style.display = isGeral ? 'none' : '';
            if (select) select.required = !isGeral;
        }

        document.querySelectorAll('input[name=destino]').forEach((el) => el.addEventListener('change', sync));
        sync();
    })();
</script>
@stop
