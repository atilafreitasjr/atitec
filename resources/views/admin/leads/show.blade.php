@extends('adminlte::page')

@section('title', 'Lead: '.$lead->name)

@section('content_header')<h1 class="mb-0">{{ $lead->name }}</h1>@stop

@section('content')
    <div class="row">
        <div class="col-md-8"><x-adminlte-card title="Detalhes" theme="info">
            <p class="mb-1"><strong>E-mail:</strong> {{ $lead->email }}</p>
            <p class="mb-1"><strong>Telefone:</strong> {{ $lead->phone ?? '—' }} • <strong>Empresa:</strong> {{ $lead->company ?? '—' }}</p>
            <p class="mb-1"><strong>Tipo:</strong> {{ $lead->project_type }} • <strong>Orçamento:</strong> {{ $lead->budget_range ?? '—' }} • <strong>Origem:</strong> {{ $lead->source }}</p>
            <hr><p class="mb-0">{{ $lead->details }}</p>
        </x-adminlte-card></div>
        <div class="col-md-4"><x-adminlte-card title="Funil comercial" theme="primary">
            <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                @csrf @method('PUT')
                <x-adminlte-select name="status" label="Status">
                    @foreach(\App\Models\Lead::STATUSES as $k => $v)<option value="{{ $k }}" @selected($lead->status === $k)>{{ $v }}</option>@endforeach
                </x-adminlte-select>
                <x-adminlte-button label="Atualizar" theme="primary" type="submit"/>
            </form>
        </x-adminlte-card></div>
    </div>
@stop
