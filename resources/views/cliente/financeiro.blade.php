@extends('adminlte::page')

@section('title', 'Financeiro')

@section('content_header')
    <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p><h1 class="mb-0">Financeiro</h1></div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif

    <x-adminlte-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Cobrança</th><th>Projeto</th><th class="text-end">Valor</th><th class="text-end">Pago</th><th class="text-end">Cortesia</th><th class="text-end">Saldo</th><th>Vencimento</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('cliente.fatura', $invoice) }}" class="fw-semibold">{{ $invoice->title }}</a>
                                <small class="d-block text-muted">{{ ucfirst($invoice->type) }}</small>
                            </td>
                            <td>{{ $invoice->project?->title ?? '—' }}</td>
                            <td class="text-end">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($invoice->paidAmount(), 2, ',', '.') }}</td>
                            <td class="text-end {{ $invoice->discountAmount() > 0 ? 'text-warning fw-semibold' : '' }}">R$ {{ number_format($invoice->discountAmount(), 2, ',', '.') }}</td>
                            <td class="text-end fw-semibold">R$ {{ number_format($invoice->balance(), 2, ',', '.') }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td><span class="badge text-bg-{{ $invoice->isOverdue() ? 'danger' : ($invoice->status === 'pago' ? 'success' : ($invoice->status === 'pendente' ? 'warning' : 'secondary')) }}">{{ $invoice->isOverdue() ? 'Vencida' : ucfirst($invoice->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Nenhuma cobrança disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </x-adminlte-card>
@stop
