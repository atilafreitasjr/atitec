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
            <thead><tr><th>Título</th><th>Cliente</th><th>Tipo</th><th class="text-end">Valor</th><th>Vencimento</th><th>Status</th><th></th></tr></thead>
            <tbody>@forelse($invoices as $f)<tr>
                <td><a href="{{ route('admin.invoices.show', $f) }}">{{ $f->title }}</a></td>
                <td>{{ $f->client?->name }}</td><td>{{ $f->type }}</td>
                <td class="text-end">R$ {{ number_format($f->amount, 2, ',', '.') }}</td>
                <td>{{ $f->due_date->format('d/m/Y') }}</td>
                <td><span class="badge bg-{{ $f->status === 'pago' ? 'success' : ($f->status === 'pendente' ? 'warning' : 'secondary') }}">{{ $f->status }}</span></td>
                <td class="text-end"><a href="{{ route('admin.invoices.edit', $f) }}" class="btn btn-sm btn-outline-primary">Editar</a></td>
            </tr>@empty<tr><td colspan="7" class="text-center">Nenhuma fatura.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $invoices->links() }}</div>
    </x-adminlte-card>
@stop
