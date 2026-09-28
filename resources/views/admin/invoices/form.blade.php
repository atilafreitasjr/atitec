@extends('adminlte::page')

@section('title', ($invoice->exists ? 'Editar' : 'Nova').' fatura')

@section('content_header')<h1 class="mb-0">{{ $invoice->exists ? 'Editar fatura' : 'Nova fatura' }}</h1>@stop

@section('content')
    <x-adminlte-card>
        <form method="POST" action="{{ $invoice->exists ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}">
            @csrf @if($invoice->exists)@method('PUT')@endif
            <div class="row">
                <div class="col-md-6"><x-adminlte-select name="client_id" label="Cliente">
                    @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id', $invoice->client_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </x-adminlte-select></div>
                <div class="col-md-6"><x-adminlte-select name="project_id" label="Projeto (opcional)">
                    <option value="">—</option>
                    @foreach($projects as $p)<option value="{{ $p->id }}" @selected(old('project_id', $invoice->project_id) == $p->id)>{{ $p->title }}</option>@endforeach
                </x-adminlte-select></div>
            </div>
            <div class="row">
                <div class="col-md-8"><x-adminlte-input name="title" label="Título" :value="old('title', $invoice->title)" required/></div>
                <div class="col-md-4"><x-adminlte-select name="type" label="Tipo">
                    @foreach(['mensalidade' => 'Mensalidade', 'servico' => 'Serviço', 'projeto' => 'Projeto'] as $k => $v)<option value="{{ $k }}" @selected(old('type', $invoice->type) === $k)>{{ $v }}</option>@endforeach
                </x-adminlte-select></div>
            </div>
            <x-adminlte-textarea name="description" label="Descrição" rows="2">{{ old('description', $invoice->description) }}</x-adminlte-textarea>
            <div class="row">
                <div class="col-md-3"><x-adminlte-input name="amount" type="number" step="0.01" label="Valor (R$)" :value="old('amount', $invoice->amount)" required/></div>
                <div class="col-md-3"><x-adminlte-input name="due_date" type="date" label="Vencimento" :value="old('due_date', optional($invoice->due_date)->format('Y-m-d'))" required/></div>
                <div class="col-md-3"><x-adminlte-input name="paid_at" type="date" label="Pago em" :value="old('paid_at', optional($invoice->paid_at)->format('Y-m-d'))"/></div>
                <div class="col-md-3"><x-adminlte-select name="status" label="Status">
                    @foreach(['pendente' => 'Pendente', 'pago' => 'Pago', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'] as $k => $v)<option value="{{ $k }}" @selected(old('status', $invoice->status) === $k)>{{ $v }}</option>@endforeach
                </x-adminlte-select></div>
            </div>
            <div class="row">
                <div class="col-md-6"><x-adminlte-input name="gateway" label="Gateway (Asaas/MP/Stripe)" :value="old('gateway', $invoice->gateway)"/></div>
                <div class="col-md-6"><x-adminlte-input name="external_id" label="ID externo" :value="old('external_id', $invoice->external_id)"/></div>
            </div>
            <x-adminlte-textarea name="notes" label="Observações" rows="2">{{ old('notes', $invoice->notes) }}</x-adminlte-textarea>
            <x-adminlte-button label="Salvar" theme="primary" type="submit"/>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-link">Cancelar</a>
        </form>
    </x-adminlte-card>
@stop
