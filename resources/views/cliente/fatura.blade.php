@extends('adminlte::page')

@section('title', $invoice->title)

@section('content_header')
    <div>
        <a href="{{ route('cliente.financeiro') }}" class="small">← Financeiro</a>
        <h1 class="mb-0">{{ $invoice->title }}</h1>
    </div>
@stop

@section('content')
    <div class="row g-3">
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-primary"><i class="bi bi-receipt"></i></span>
                <div class="info-box-content"><span class="info-box-text">Valor da cobrança</span><span class="info-box-number">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-success"><i class="bi bi-check2-circle"></i></span>
                <div class="info-box-content"><span class="info-box-text">Total pago</span><span class="info-box-number">R$ {{ number_format($invoice->paidAmount(), 2, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-warning"><i class="bi bi-gift"></i></span>
                <div class="info-box-content"><span class="info-box-text">Cortesia ATITEC</span><span class="info-box-number">R$ {{ number_format($invoice->discountAmount(), 2, ',', '.') }}</span></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box"><span class="info-box-icon text-bg-{{ $invoice->balance() > 0 ? 'danger' : 'success' }}"><i class="bi bi-wallet2"></i></span>
                <div class="info-box-content"><span class="info-box-text">Saldo</span><span class="info-box-number">R$ {{ number_format($invoice->balance(), 2, ',', '.') }}</span></div>
            </div>
        </div>
    </div>

    <x-adminlte-card icon="bi bi-info-circle">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <p class="text-muted mb-1">{{ $invoice->project?->title ?? 'Cobrança ATITEC' }} · {{ ucfirst($invoice->type) }}</p>
                <p class="mb-1">Vencimento: <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong></p>
                @if($invoice->description)<p class="mt-3 mb-0 text-break" style="white-space:pre-line">{{ $invoice->description }}</p>@endif
            </div>
            <span class="badge text-bg-{{ $invoice->isOverdue() ? 'danger' : ($invoice->status === 'pago' ? 'success' : ($invoice->status === 'pendente' ? 'warning' : 'secondary')) }} fs-6">{{ $invoice->isOverdue() ? 'Vencida' : ucfirst($invoice->status) }}</span>
        </div>

        @if($invoice->discountAmount() > 0)
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-gift me-1"></i>
                <strong>Cortesia da ATITEC:</strong> R$ {{ number_format($invoice->discountAmount(), 2, ',', '.') }}
                @if($invoice->discount_reason) — {{ $invoice->discount_reason }} @endif
                <small class="d-block">Diferença assumida pela ATITEC (desconto aplicado, sem necessidade de pagamento).</small>
            </div>
        @endif

        @if($invoice->pdf_path)
            <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary mt-3"><i class="bi bi-file-earmark-pdf me-1"></i> Baixar boleto / documento PDF</a>
        @endif
    </x-adminlte-card>

    <x-adminlte-card title="Pagamentos registrados" icon="bi bi-clock-history">
        <div class="list-group list-group-flush">
            @forelse($invoice->payments as $payment)
                <div class="list-group-item px-0 d-flex flex-wrap justify-content-between gap-2">
                    <span>{{ $payment->paid_at?->format('d/m/Y') }} · {{ ucfirst($payment->method) }}@if($payment->notes) · {{ $payment->notes }}@endif</span>
                    <strong>R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</strong>
                </div>
            @empty
                <p class="text-muted mb-0">Ainda não há pagamentos registrados nesta cobrança.</p>
            @endforelse
        </div>
    </x-adminlte-card>
@stop
