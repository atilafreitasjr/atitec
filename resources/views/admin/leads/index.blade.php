@extends('adminlte::page')

@section('title', 'Leads / Orçamentos')

@section('content_header')<h1 class="mb-0">Leads / Orçamentos</h1>@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Nome</th><th>Tipo</th><th>Contato</th><th>Status</th><th>Data</th><th></th></tr></thead>
            <tbody>@forelse($leads as $l)<tr>
                <td><a href="{{ route('admin.leads.show', $l) }}">{{ $l->name }}</a></td>
                <td>{{ $l->project_type }}</td><td>{{ $l->email }}<br><small>{{ $l->phone }}</small></td>
                <td><span class="badge bg-secondary">{{ $l->status }}</span></td><td>{{ $l->created_at->format('d/m/Y') }}</td>
                <td><a href="{{ route('admin.leads.show', $l) }}" class="btn btn-sm btn-outline-primary">Abrir</a></td>
            </tr>@empty<tr><td colspan="6" class="text-center">Nenhum lead.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $leads->links() }}</div>
    </x-adminlte-card>
@stop
