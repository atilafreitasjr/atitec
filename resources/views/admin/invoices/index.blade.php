@extends('adminlte::page')

@section('title', 'Faturas e cobranças')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Faturas e cobranças</h1>
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Nova fatura</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Cobrança</th><th>Cliente / projeto</th><th class="text-end">Valor</th><th class="text-end">Saldo</th><th>Vencimento</th><th>Status</th><th></th></tr></thead>
            <tbody>@forelse($invoices as $f)
                @php $paid = (float) $f->payments->sum('amount'); $balance = max(0, (float) $f->amount - (float) $f->discount_amount - $paid); @endphp
                <tr>
                    <td><a class="fw-semibold" href="{{ route('admin.invoices.show', $f) }}">{{ $f->title }}</a><small class="d-block text-muted">{{ ucfirst($f->type) }} · #{{ $f->id }}</small></td>
                    <td>{{ $f->client?->name }}<small class="d-block text-muted">{{ $f->project?->title ?? 'Cobrança geral' }}</small></td>
                    <td class="text-end">R$ {{ number_format($f->amount, 2, ',', '.') }}</td>
                    <td class="text-end fw-semibold">R$ {{ number_format($balance, 2, ',', '.') }}</td>
                    <td>{{ $f->due_date->format('d/m/Y') }}</td>
                    <td><span class="badge text-bg-{{ $f->isOverdue() ? 'danger' : ($f->status === 'pago' ? 'success' : ($f->status === 'pendente' ? 'warning' : 'secondary')) }}">{{ $f->isOverdue() ? 'Vencida' : ucfirst($f->status) }}</span></td>
                    <td class="text-end text-nowrap"><a href="{{ route('admin.invoices.show', $f) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td>
                </tr>
            @empty<tr><td colspan="7" class="text-center">Nenhuma cobrança cadastrada.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </x-adminlte-card>
@stop
