@extends('adminlte::page')

@section('title', $client->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $client->name }}</h1>
        <div class="d-flex gap-2">
            @if(! $client->user_id)
                <a href="{{ route('admin.users.create', ['client_id' => $client->id]) }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Criar acesso ao workspace</a>
            @endif
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-outline-primary">Editar</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4"><x-adminlte-card title="Dados" theme="primary">
            <p class="mb-1"><strong>Empresa:</strong> {{ $client->company ?? '—' }}</p>
            <p class="mb-1"><strong>E-mail:</strong> {{ $client->email }}</p>
            <p class="mb-1"><strong>Telefone:</strong> {{ $client->phone ?? '—' }}</p>
            <p class="mb-1"><strong>CPF:</strong> {{ $client->formatted_cpf ?? '—' }}</p>
            <p class="mb-1"><strong>CNPJ:</strong> {{ $client->formatted_cnpj ?? '—' }}</p>
            <p class="mb-1"><strong>Endereço:</strong> {{ $client->full_address ?? '—' }}</p>
            @if ($client->company)
                <p class="mb-0"><strong>Endereço da empresa:</strong>
                    {{ $client->company_same_address ? 'O mesmo do cliente' : ($client->company_full_address ?? '—') }}
                </p>
            @endif
        </x-adminlte-card></div>
        <div class="col-md-4"><x-adminlte-card title="Projetos" theme="info">
            <ul class="list-group list-group-flush">@forelse($client->projects as $p)<li class="list-group-item">{{ $p->title }}</li>@empty<li class="list-group-item">Nenhum.</li>@endforelse</ul>
        </x-adminlte-card></div>
        <div class="col-md-4"><x-adminlte-card title="Faturas" theme="warning">
            <ul class="list-group list-group-flush">@forelse($client->invoices as $f)<li class="list-group-item d-flex justify-content-between"><span>{{ $f->title }}</span><span class="badge bg-secondary">{{ $f->status }}</span></li>@empty<li class="list-group-item">Nenhuma.</li>@endforelse</ul>
        </x-adminlte-card></div>
    </div>
@stop
