@extends('adminlte::page')

@section('title', 'Orçamentos e propostas')

@section('content_header')
    <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p><h1 class="mb-0">Orçamentos e propostas</h1></div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    @forelse($leads as $lead)
        <x-adminlte-card>
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                <div>
                    <p class="text-uppercase small text-muted mb-1">Pedido {{ $lead->created_at->format('d/m/Y') }} · {{ $lead->project_type ?: 'Consulta' }}</p>
                    <h4 class="mb-0">{{ $lead->proposal_title ?: 'Pedido de orçamento' }}</h4>
                </div>
                <span class="badge text-bg-{{ $lead->proposal_status === 'enviada' ? 'warning' : ($lead->proposal_status === 'aceita' || $lead->proposal_status === 'convertida' ? 'success' : ($lead->proposal_status === 'recusada' ? 'secondary' : 'info')) }}">
                    {{ \App\Models\Lead::PROPOSAL_STATUSES[$lead->proposal_status] ?? ucfirst($lead->proposal_status) }}
                </span>
            </div>

            @if($lead->details)
                <hr>
                <p class="text-uppercase small text-muted mb-1">Sua solicitação</p>
                <p class="mb-0 text-break" style="white-space:pre-line">{{ $lead->details }}</p>
            @endif

            @if($lead->proposal_sent_at)
                <div class="rounded bg-body-secondary p-3 mt-3">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small text-muted mb-1">Proposta comercial</p>
                            <h3 class="mb-0">R$ {{ number_format((float) $lead->proposal_amount, 2, ',', '.') }}</h3>
                        </div>
                        <span class="small text-muted">{{ $lead->proposal_valid_until ? 'Válida até '.$lead->proposal_valid_until->format('d/m/Y') : 'Sem prazo de validade informado' }}</span>
                    </div>
                    <h5 class="mt-3">{{ $lead->proposal_title }}</h5>
                    <p class="mb-0 text-break" style="white-space:pre-line">{{ $lead->proposal_summary }}</p>

                    @if($lead->proposal_status === 'enviada' && $lead->canRespondToProposal())
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <form method="POST" action="{{ route('cliente.orcamentos.responder', $lead) }}">
                                @csrf<input type="hidden" name="resposta" value="aceita">
                                <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Aceitar proposta</button>
                            </form>
                            <form method="POST" action="{{ route('cliente.orcamentos.responder', $lead) }}" onsubmit="return confirm('Deseja recusar esta proposta?')">
                                @csrf<input type="hidden" name="resposta" value="recusada">
                                <button class="btn btn-outline-secondary">Recusar</button>
                            </form>
                        </div>
                    @elseif($lead->convertedProject)
                        <a href="{{ route('cliente.projeto', $lead->convertedProject) }}" class="btn btn-outline-primary mt-3">Acompanhar projeto criado →</a>
                    @elseif($lead->proposal_status === 'aceita')
                        <div class="alert alert-success mt-3 mb-0">Proposta aceita. A equipe ATITEC está preparando o início do projeto.</div>
                    @elseif($lead->proposal_status === 'enviada' && $lead->proposal_valid_until?->isPast())
                        <div class="alert alert-warning mt-3 mb-0">Esta proposta expirou. Entre em contato com a ATITEC para solicitar uma atualização.</div>
                    @endif
                </div>
            @else
                <p class="text-muted mt-3 mb-0">Pedido recebido. A proposta aparecerá aqui quando a ATITEC responder.</p>
            @endif
        </x-adminlte-card>
    @empty
        <x-adminlte-card>
            <div class="text-center py-4">
                <h4>Nenhum pedido de orçamento associado</h4>
                <p class="text-muted">Quando a ATITEC vincular um pedido ao seu cliente, ele aparecerá aqui.</p>
                <a href="{{ route('site.orcamento') }}" class="btn btn-primary">Solicitar orçamento</a>
            </div>
        </x-adminlte-card>
    @endforelse

    <div>{{ $leads->links() }}</div>
@stop
