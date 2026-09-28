@extends('adminlte::page')

@section('title', $invoice->title)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $invoice->title }}</h1>
        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-primary">Editar</a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6"><x-adminlte-card title="Fatura" theme="warning">
            <p class="mb-1"><strong>Cliente:</strong> {{ $invoice->client?->name }}</p>
            <p class="mb-1"><strong>Valor:</strong> R$ {{ number_format($invoice->amount, 2, ',', '.') }}</p>
            <p class="mb-1"><strong>Vencimento:</strong> {{ $invoice->due_date->format('d/m/Y') }} — <span class="badge bg-secondary">{{ $invoice->status }}</span></p>
            <p class="mb-0"><strong>Tipo:</strong> {{ $invoice->type }} @if($invoice->gateway) • {{ $invoice->gateway }} @endif</p>
            @if($invoice->description)<p class="mt-2 mb-0">{{ $invoice->description }}</p>@endif
        </x-adminlte-card></div>
        <div class="col-md-6"><x-adminlte-card title="Pagamentos" theme="success">
            <ul class="list-group list-group-flush">@forelse($invoice->payments as $pay)<li class="list-group-item d-flex justify-content-between"><span>{{ $pay->paid_at->format('d/m/Y') }} • {{ $pay->method }}</span><strong>R$ {{ number_format($pay->amount, 2, ',', '.') }}</strong></li>@empty<li class="list-group-item">Nenhum pagamento registrado.</li>@endforelse</ul>
        </x-adminlte-card></div>
    </div>
@stop
