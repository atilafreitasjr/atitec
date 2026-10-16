@extends('adminlte::page')

@section('title', 'Lead: '.$lead->name)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <p class="text-uppercase small text-muted mb-1">Pedido de orçamento</p>
            <h1 class="mb-0">{{ $lead->name }}</h1>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary">Voltar aos pedidos</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="Atualizado">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Verifique os dados">{{ $errors->first() }}</x-adminlte-alert>@endif

    <div class="row g-3">
        <div class="col-lg-7">
            <x-adminlte-card title="Pedido do cliente" theme="primary" icon="bi bi-chat-square-text">
                <div class="row mb-3">
                    <div class="col-md-6"><span class="text-muted small">E-mail</span><div>{{ $lead->email }}</div></div>
                    <div class="col-md-6"><span class="text-muted small">Telefone / WhatsApp</span><div>{{ $lead->phone ?: '—' }}</div></div>
                    <div class="col-md-6 mt-3"><span class="text-muted small">Empresa</span><div>{{ $lead->company ?: '—' }}</div></div>
                    <div class="col-md-6 mt-3"><span class="text-muted small">Tipo de projeto</span><div>{{ $lead->project_type ?: '—' }}</div></div>
                    <div class="col-md-6 mt-3"><span class="text-muted small">Faixa de orçamento indicada</span><div>{{ $lead->budget_range ?: '—' }}</div></div>
                    <div class="col-md-6 mt-3"><span class="text-muted small">Origem</span><div>{{ $lead->source }}</div></div>
                </div>
                <div class="rounded-3 p-3 bg-body-secondary">
                    <div class="text-muted small mb-1">Necessidade informada</div>
                    <div class="text-break">{!! $lead->details ? \App\Support\Markdown::render($lead->details) : 'Nenhum detalhe informado.' !!}</div>
                </div>
            </x-adminlte-card>

            @if($lead->requirements)
                @php $req = $lead->requirements; @endphp
                <x-adminlte-card title="Perfil do orçamento e estimativa" theme="info" icon="bi bi-calculator">
                    <div class="row g-2 small">
                        @if(! empty($req['type']))
                            <div class="col-md-6"><span class="text-muted">Tipo de projeto</span><div class="fw-semibold">{{ $req['type'] }} <span class="text-muted">({{ number_format((float) ($req['type_hours'] ?? 0), 0, ',', '.') }} h base)</span></div></div>
                        @endif
                        @if(! empty($req['deadline']))
                            <div class="col-md-6"><span class="text-muted">Prazo</span><div class="fw-semibold">{{ $req['deadline']['label'] }} <span class="text-muted">(×{{ number_format((float) $req['deadline']['multiplier'], 2, ',', '.') }})</span></div></div>
                        @endif
                    </div>

                    @if(! empty($req['scope']))
                        <hr class="my-2">
                        <span class="text-muted small">Necessidades marcadas</span>
                        <ul class="mb-2 small">
                            @foreach($req['scope'] as $item)
                                <li>{{ $item['label'] }} — {{ number_format((float) $item['hours'], 0, ',', '.') }} h (R$ {{ number_format((float) $item['cost'], 2, ',', '.') }})@if(! empty($item['monthly'])) + R$ {{ number_format((float) $item['monthly'], 2, ',', '.') }}/mês @endif</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(! empty($req['infrastructure']))
                        <span class="text-muted small">Infraestrutura e serviços</span>
                        <ul class="mb-2 small">
                            @foreach($req['infrastructure'] as $item)
                                <li>{{ $item['label'] }}@if(! empty($item['setup'])) — implantação R$ {{ number_format((float) $item['setup'], 2, ',', '.') }}@endif @if(! empty($item['monthly'])) + R$ {{ number_format((float) $item['monthly'], 2, ',', '.') }}/mês @endif</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="rounded-3 p-3 bg-body-secondary small">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <span><strong>{{ number_format((float) ($req['hours'] ?? 0), 0, ',', '.') }} h</strong> × R$ {{ number_format((float) ($req['hourly_rate'] ?? 0), 2, ',', '.') }} = <strong>R$ {{ number_format((float) ($req['development'] ?? 0), 2, ',', '.') }}</strong>@if((float) ($req['setup'] ?? 0) > 0) + <strong>R$ {{ number_format((float) $req['setup'], 2, ',', '.') }}</strong> de implantação @endif</span>
                            <span>Total estimado: <strong>R$ {{ number_format((float) ($req['total'] ?? 0), 2, ',', '.') }}</strong></span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between gap-2 mt-1">
                            <span>Faixa apresentada: <strong>R$ {{ number_format((float) ($req['range']['min'] ?? 0), 0, ',', '.') }} a R$ {{ number_format((float) ($req['range']['max'] ?? 0), 0, ',', '.') }}</strong></span>
                            @if((float) ($req['monthly'] ?? 0) > 0)
                                <span>Recorrente: <strong>R$ {{ number_format((float) $req['monthly'], 2, ',', '.') }}/mês</strong></span>
                            @endif
                        </div>
                    </div>
                </x-adminlte-card>
            @endif

            <x-adminlte-card title="Proposta comercial" theme="warning" icon="bi bi-file-earmark-text">
                @if($lead->proposal_status === 'enviada' || $lead->proposal_status === 'aceita' || $lead->proposal_status === 'recusada' || $lead->proposal_status === 'convertida')
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                        <span class="badge text-bg-{{ $lead->proposal_status === 'aceita' || $lead->proposal_status === 'convertida' ? 'success' : ($lead->proposal_status === 'recusada' ? 'danger' : 'warning') }}">{{ \App\Models\Lead::PROPOSAL_STATUSES[$lead->proposal_status] ?? $lead->proposal_status }}</span>
                        <span class="fw-bold">R$ {{ number_format((float) $lead->proposal_amount, 2, ',', '.') }}</span>
                    </div>
                    <h3 class="h5">{{ $lead->proposal_title }}</h3>
                    <p class="text-break" style="white-space:pre-wrap">{{ $lead->proposal_summary }}</p>
                    <p class="small text-muted mb-3">
                        Enviada: {{ $lead->proposal_sent_at?->format('d/m/Y H:i') ?? '—' }}
                        @if($lead->proposal_valid_until) · Válida até {{ $lead->proposal_valid_until->format('d/m/Y') }} @endif
                        @if($lead->proposal_responded_at) · Respondida {{ $lead->proposal_responded_at->format('d/m/Y H:i') }} @endif
                    </p>
                    @if($lead->proposal_status === 'enviada' && $lead->client?->phone)
                        @php
                            $proposalPhone = preg_replace('/\D+/', '', $lead->client->phone);
                            if ($proposalPhone && ! str_starts_with($proposalPhone, '55')) $proposalPhone = '55'.$proposalPhone;
                            $proposalMessage = 'Olá, '.$lead->client->name.'. Sua proposta "'.$lead->proposal_title.'" está disponível no workspace ATITEC.';
                            $proposalMessage .= $lead->client->user_id
                                ? ' Acesse: '.route('cliente.orcamentos')
                                : ' Responda esta mensagem para receber o acesso ao workspace.';
                        @endphp
                        <a class="btn btn-sm btn-success mb-3" target="_blank" rel="noopener noreferrer" href="https://wa.me/{{ $proposalPhone }}?text={{ rawurlencode($proposalMessage) }}"><i class="bi bi-whatsapp me-1"></i> Avisar pelo WhatsApp</a>
                    @endif
                    @if($lead->proposal_status === 'aceita' && ! $lead->converted_project_id)
                        <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" onsubmit="return confirm('Criar projeto e associá-lo ao cliente?')">
                            @csrf
                            <button class="btn btn-primary"><i class="bi bi-kanban me-1"></i> Criar projeto a partir da proposta</button>
                        </form>
                    @elseif($lead->convertedProject)
                        <a href="{{ route('admin.projects.show', $lead->convertedProject) }}" class="btn btn-outline-primary">Abrir projeto {{ $lead->convertedProject->title }}</a>
                    @endif
                @else
                    <p class="text-muted mb-0">Ainda não há uma proposta enviada ao workspace do cliente.</p>
                @endif
            </x-adminlte-card>
        </div>

        <div class="col-lg-5">
            <x-adminlte-card title="Vincular ao workspace" theme="info" icon="bi bi-person-check">
                <p class="text-muted small">Associe este pedido ao cliente para disponibilizá-lo na área do cliente.</p>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                    @csrf @method('PUT')
                    <x-adminlte-select name="client_id" label="Cliente">
                        <option value="">— Ainda não associado —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id', $lead->client_id) == $client->id)>{{ $client->name }} ({{ $client->email }})</option>
                        @endforeach
                    </x-adminlte-select>
                    <x-adminlte-select name="status" label="Etapa do funil">
                        @foreach(\App\Models\Lead::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $lead->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                    <button class="btn btn-outline-primary">Salvar vínculo e etapa</button>
                </form>
            </x-adminlte-card>

            <x-adminlte-card title="Preparar proposta" theme="primary" icon="bi bi-send">
                @if(! $lead->client_id)
                    <div class="alert alert-warning mb-0">Vincule o pedido a um cliente antes de enviar uma proposta.</div>
                @elseif($lead->converted_project_id)
                    <div class="alert alert-success mb-0">Este pedido já foi convertido em projeto.</div>
                @else
                    <form method="POST" action="{{ route('admin.leads.proposal', $lead) }}">
                        @csrf
                        <x-adminlte-input name="proposal_title" label="Título da proposta" :value="old('proposal_title', $lead->proposal_title ?: 'Proposta — '.$lead->project_type)" required/>
                        <x-adminlte-textarea name="proposal_summary" label="Escopo e condições" rows="6" required>{{ old('proposal_summary', $lead->proposal_summary) }}</x-adminlte-textarea>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="proposal_amount_display" class="form-label">Investimento (R$)</label>
                                    <input id="proposal_amount_display" type="text" inputmode="numeric" autocomplete="off" required placeholder="0,00"
                                           data-mask="money" data-mask-raw="proposal_amount" class="form-control @error('proposal_amount') is-invalid @enderror">
                                    <input type="hidden" name="proposal_amount" value="{{ old('proposal_amount', $lead->proposal_amount) }}">
                                    @error('proposal_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6"><x-adminlte-input name="proposal_valid_until" type="date" label="Válida até" :value="old('proposal_valid_until', optional($lead->proposal_valid_until)->format('Y-m-d'))"/></div>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i> Disponibilizar proposta</button>
                    </form>
                @endif
            </x-adminlte-card>
        </div>
    </div>
@stop
