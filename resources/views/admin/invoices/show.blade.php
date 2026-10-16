@extends('adminlte::page')

@section('title', $invoice->title)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><p class="text-uppercase small text-muted mb-1">Financeiro · Fatura #{{ $invoice->id }}</p><h1 class="mb-0">{{ $invoice->title }}</h1></div>
        <div class="d-flex flex-wrap gap-2">
            @if($invoice->client?->phone)
                @php
                    $whatsappPhone = preg_replace('/\D+/', '', $invoice->client->phone);
                    if ($whatsappPhone && ! str_starts_with($whatsappPhone, '55')) $whatsappPhone = '55'.$whatsappPhone;
                    $message = 'Olá, '.$invoice->client->name.'. Segue a cobrança '.$invoice->title.' no valor de R$ '.number_format((float) $invoice->amount, 2, ',', '.').', com vencimento em '.$invoice->due_date->format('d/m/Y').'.';
                    if ($invoice->balance() > 0) {
                        $message .= ' Saldo atual: R$ '.number_format($invoice->balance(), 2, ',', '.').'.';
                    }
                    if ($invoice->client->user_id) {
                        $message .= ' Consulte o boleto e os detalhes no seu workspace: '.route('cliente.fatura', $invoice);
                    } else {
                        $message .= ' Para receber o boleto digital, responda esta mensagem ou solicite o acesso ao workspace ATITEC.';
                    }
                @endphp
                <a href="https://wa.me/{{ $whatsappPhone }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener noreferrer" class="btn btn-success"><i class="bi bi-whatsapp me-1"></i> Preparar WhatsApp</a>
            @endif
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-primary">Editar</a>
            @can('invoice.delete')
                <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Excluir esta fatura? As tarefas vinculadas voltarão a ficar disponíveis para faturamento e os pagamentos registrados serão removidos. Esta ação não pode ser desfeita.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Excluir</button>
                </form>
            @endcan
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="Atualizado">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Verifique os dados">{{ $errors->first() }}</x-adminlte-alert>@endif

    <div class="row g-3">
        <div class="col-lg-8">
            <x-adminlte-card title="Resumo da cobrança" theme="primary" icon="bi bi-receipt">
                <div class="row g-3">
                    <div class="col-sm-6"><span class="text-muted small">Cliente</span><div class="fw-semibold">{{ $invoice->client?->name ?? '—' }}</div></div>
                    <div class="col-sm-6"><span class="text-muted small">Projeto</span><div class="fw-semibold">{{ $invoice->project?->title ?? 'Cobrança geral' }}</div></div>
                    <div class="col-sm-3"><span class="text-muted small">Valor</span><div class="h4 mb-0">R$ {{ number_format((float) $invoice->amount, 2, ',', '.') }}</div></div>
                    <div class="col-sm-3"><span class="text-muted small">Pago</span><div class="h4 mb-0 text-success">R$ {{ number_format($invoice->paidAmount(), 2, ',', '.') }}</div></div>
                    <div class="col-sm-3"><span class="text-muted small">Cortesia ATITEC</span><div class="h4 mb-0 text-warning">R$ {{ number_format($invoice->discountAmount(), 2, ',', '.') }}</div></div>
                    <div class="col-sm-3"><span class="text-muted small">Saldo</span><div class="h4 mb-0">R$ {{ number_format($invoice->balance(), 2, ',', '.') }}</div></div>
                    <div class="col-sm-6"><span class="text-muted small">Vencimento</span><div>{{ $invoice->due_date->format('d/m/Y') }}</div></div>
                    <div class="col-sm-6"><span class="text-muted small">Status</span><div><span class="badge text-bg-{{ $invoice->isOverdue() ? 'danger' : ($invoice->status === 'pago' ? 'success' : ($invoice->status === 'pendente' ? 'warning' : 'secondary')) }}">{{ $invoice->isOverdue() ? 'Vencida' : ucfirst($invoice->status) }}</span></div></div>
                </div>
                @if($invoice->discountAmount() > 0)
                    <hr>
                    <p class="mb-0"><i class="bi bi-gift me-1 text-warning"></i>
                        <strong>Cortesia/desconto aplicado pela ATITEC:</strong>
                        R$ {{ number_format($invoice->discountAmount(), 2, ',', '.') }}
                        @if($invoice->discount_reason) — {{ $invoice->discount_reason }} @endif
                    </p>
                @endif
                @if($invoice->description)<hr><div class="text-break" style="white-space:pre-wrap">{{ $invoice->description }}</div>@endif
                @if($invoice->pdf_path)
                    <a class="btn btn-outline-primary mt-3" href="{{ route('invoices.pdf', $invoice) }}"><i class="bi bi-file-earmark-pdf me-1"></i> Baixar boleto / PDF</a>
                @else
                    <p class="small text-muted mt-3 mb-0">Nenhum boleto/PDF anexado.</p>
                @endif
            </x-adminlte-card>

            @if($invoice->billedTasks->isNotEmpty())
                <x-adminlte-card title="Tarefas incluídas nesta cobrança" theme="info" icon="bi bi-list-check">
                    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Tarefa</th><th>Entrega</th><th class="text-end">Horas</th><th class="text-end">Valor</th></tr></thead>
                        <tbody>@foreach($invoice->billedTasks as $task)<tr>
                            <td>{{ $task->title }}</td><td>{{ $task->deliverable?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $task->hours_actual, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format((float) $task->cost_actual, 2, ',', '.') }}</td>
                        </tr>@endforeach</tbody>
                    </table></div>
                </x-adminlte-card>
            @endif
        </div>

        <div class="col-lg-4">
            @can('invoice.edit')
                <x-adminlte-card title="Cortesia / desconto ATITEC" theme="warning" icon="bi bi-gift">
                    <p class="small text-muted">Declare a diferença em aberto como cortesia da ATITEC (assumida pela empresa). O saldo e o status da fatura são recalculados automaticamente.</p>
                    <form method="POST" action="{{ route('admin.invoices.discount', $invoice) }}">
                        @csrf
                        <x-adminlte-input name="discount_amount" type="number" step="0.01" min="0" max="{{ number_format((float) $invoice->amount - $invoice->paidAmount(), 2, '.', '') }}"
                                          label="Valor da cortesia (R$)" :value="old('discount_amount', number_format($invoice->discountAmount(), 2, '.', ''))" required/>
                        <x-adminlte-input name="discount_reason" label="Motivo / observação" :value="old('discount_reason', $invoice->discount_reason)" placeholder="Cortesia ATITEC — diferença assumida pela empresa"/>
                        <button class="btn btn-warning w-100"><i class="bi bi-gift me-1"></i> {{ $invoice->discountAmount() > 0 ? 'Atualizar cortesia' : 'Aplicar cortesia' }}</button>
                        @if($invoice->discountAmount() > 0)
                            <p class="small text-muted mt-2 mb-0">Para remover a cortesia, salve com valor <strong>0</strong>.</p>
                        @endif
                    </form>
                </x-adminlte-card>

                <x-adminlte-card title="Registrar pagamento" theme="success" icon="bi bi-cash-coin">
                    @if($invoice->balance() > 0 && $invoice->status !== 'cancelado')
                        <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}">
                            @csrf
                            <x-adminlte-input name="amount" type="number" step="0.01" min="0.01" max="{{ number_format($invoice->balance(), 2, '.', '') }}" label="Valor recebido (R$)" :value="old('amount', number_format($invoice->balance(), 2, '.', ''))" required/>
                            <x-adminlte-input name="paid_at" type="date" label="Data do recebimento" :value="old('paid_at', today()->format('Y-m-d'))"/>
                            <x-adminlte-select name="method" label="Forma de pagamento">
                                @foreach(['pix' => 'PIX', 'boleto' => 'Boleto', 'transferencia' => 'Transferência', 'dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'manual' => 'Manual'] as $key => $label)
                                    <option value="{{ $key }}" @selected(old('method', 'pix') === $key)>{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                            <x-adminlte-textarea name="notes" label="Observação" rows="2">{{ old('notes') }}</x-adminlte-textarea>
                            <button class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i> Registrar recebimento</button>
                        </form>
                    @else
                        <p class="mb-0 text-muted">{{ $invoice->status === 'cancelado' ? 'Esta cobrança foi cancelada.' : 'Esta fatura está totalmente paga ou abatida por cortesia.' }}</p>
                    @endif
                </x-adminlte-card>
            @endcan

            <x-adminlte-card title="Histórico de pagamentos" theme="secondary" icon="bi bi-clock-history">
                <div class="list-group list-group-flush">
                    @forelse($invoice->payments as $payment)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between gap-2">
                                <span><strong>{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</strong><small class="d-block text-muted">{{ ucfirst($payment->method) }} @if($payment->notes) · {{ $payment->notes }} @endif</small></span>
                                <span class="text-nowrap"><strong>R$ {{ number_format((float) $payment->amount, 2, ',', '.') }}</strong></span>
                            </div>
                            @can('invoice.edit')
                                @if($invoice->status !== 'cancelado')
                                    <div class="d-flex gap-2 mt-1">
                                        <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#payment-edit-{{ $payment->id }}">Editar</button>
                                        <form method="POST" action="{{ route('admin.invoices.payments.destroy', [$invoice, $payment]) }}" onsubmit="return confirm('Remover este pagamento? O saldo da fatura será recalculado.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link text-danger p-0">Remover</button>
                                        </form>
                                    </div>
                                    <div class="collapse mt-2" id="payment-edit-{{ $payment->id }}">
                                        <form method="POST" action="{{ route('admin.invoices.payments.update', [$invoice, $payment]) }}" class="rounded border p-2">
                                            @csrf @method('PUT')
                                            <div class="row g-2">
                                                <div class="col-6"><label class="form-label small mb-0">Valor (R$)</label><input type="number" step="0.01" min="0.01" name="amount" value="{{ number_format((float) $payment->amount, 2, '.', '') }}" class="form-control form-control-sm" required></div>
                                                <div class="col-6"><label class="form-label small mb-0">Data</label><input type="date" name="paid_at" value="{{ $payment->paid_at?->format('Y-m-d') }}" class="form-control form-control-sm"></div>
                                                <div class="col-6"><label class="form-label small mb-0">Forma</label><select name="method" class="form-select form-select-sm">
                                                    @foreach(['pix' => 'PIX', 'boleto' => 'Boleto', 'transferencia' => 'Transferência', 'dinheiro' => 'Dinheiro', 'cartao' => 'Cartão', 'manual' => 'Manual'] as $key => $label)
                                                        <option value="{{ $key }}" @selected($payment->method === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select></div>
                                                <div class="col-6"><label class="form-label small mb-0">Observação</label><input type="text" name="notes" value="{{ $payment->notes }}" class="form-control form-control-sm"></div>
                                            </div>
                                            <button class="btn btn-sm btn-primary mt-2">Salvar pagamento</button>
                                        </form>
                                    </div>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <p class="small text-muted mb-0">Nenhum pagamento registrado.</p>
                    @endforelse
                </div>
            </x-adminlte-card>
        </div>
    </div>
@stop
