@extends('adminlte::page')

@section('title', 'Leads / Orçamentos')

@section('content_header')
    <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Funil comercial</p><h1 class="mb-0">Pedidos de orçamento</h1><p class="text-muted mb-0 mt-1">Associe o pedido a um cliente, envie a proposta e acompanhe o aceite.</p></div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="Atualizado">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-5"><label for="lead-search" class="form-label">Buscar pedido</label><input id="lead-search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nome, e-mail, empresa ou projeto"></div>
            <div class="col-md-3"><label class="form-label">Etapa</label><select name="status" class="form-select"><option value="">Todas</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">Workspace</label><select name="vinculo" class="form-select"><option value="">Todos</option><option value="pendente" @selected(request('vinculo') === 'pendente')>Sem cliente</option></select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Filtrar</button><a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">Limpar</a></div>
        </form>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Solicitante</th><th>Pedido</th><th>Cliente / workspace</th><th>Proposta</th><th>Etapa</th><th>Recebido</th><th></th></tr></thead>
            <tbody>@forelse($leads as $lead)
                <tr>
                    <td><a class="fw-semibold" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a><small class="d-block text-muted">{{ $lead->email }} · {{ $lead->phone ?: 'sem telefone' }}</small></td>
                    <td>{{ $lead->project_type ?: 'Contato' }}<small class="d-block text-muted">Faixa indicada: {{ $lead->budget_range ?: '—' }}</small></td>
                    <td>@if($lead->client)<span class="fw-semibold">{{ $lead->client->name }}</span><small class="d-block text-success">Vinculado</small>@else<span class="badge text-bg-warning">Vincular cliente</span>@endif</td>
                    <td>@if($lead->proposal_status === 'rascunho')<span class="text-muted">Rascunho</span>@else<span class="fw-semibold">R$ {{ number_format((float) $lead->proposal_amount, 2, ',', '.') }}</span><small class="d-block text-muted">{{ \App\Models\Lead::PROPOSAL_STATUSES[$lead->proposal_status] ?? $lead->proposal_status }}</small>@endif</td>
                    <td><span class="badge text-bg-secondary">{{ $statuses[$lead->status] ?? $lead->status }}</span></td>
                    <td class="text-nowrap">{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td class="text-end"><a href="{{ route('admin.leads.show', $lead) }}" class="btn btn-sm btn-outline-primary">Abrir workspace</a></td>
                </tr>
            @empty<tr><td colspan="7" class="py-5 text-center text-muted">Nenhum pedido encontrado.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="mt-3">{{ $leads->links() }}</div>
    </x-adminlte-card>
@stop
