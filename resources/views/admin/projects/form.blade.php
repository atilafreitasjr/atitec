@extends('adminlte::page')

@section('title', ($project->exists ? 'Editar' : 'Novo').' projeto')

@section('content_header')
    <h1 class="mb-0">{{ $project->exists ? 'Editar projeto' : 'Novo projeto' }}</h1>
@stop

@section('content')
    <x-adminlte-card>
        <form method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}">
            @csrf
            @if($project->exists)@method('PUT')@endif
            <div class="row">
                <div class="col-md-6"><x-adminlte-input name="title" label="Título" :value="old('title', $project->title)" required/></div>
                <div class="col-md-3"><x-adminlte-input name="slug" label="Slug (vazio = automático)" :value="old('slug', $project->slug)"/></div>
                <div class="col-md-3"><x-adminlte-select name="client_id" label="Cliente">
                    <option value="">—</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id', $project->client_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </x-adminlte-select></div>
            </div>
            <x-adminlte-textarea name="description" label="Descrição (site)" rows="3">{{ old('description', $project->description) }}</x-adminlte-textarea>
            <div class="row">
                <div class="col-md-4"><x-adminlte-textarea name="problem" label="Problema" rows="2">{{ old('problem', $project->problem) }}</x-adminlte-textarea></div>
                <div class="col-md-4"><x-adminlte-textarea name="solution" label="Solução" rows="2">{{ old('solution', $project->solution) }}</x-adminlte-textarea></div>
                <div class="col-md-4"><x-adminlte-textarea name="results" label="Resultados" rows="2">{{ old('results', $project->results) }}</x-adminlte-textarea></div>
            </div>
            <div class="row">
                <div class="col-md-4"><x-adminlte-input name="url" label="URL" :value="old('url', $project->url)"/></div>
                <div class="col-md-4"><x-adminlte-input name="segment" label="Segmento" :value="old('segment', $project->segment)"/></div>
                <div class="col-md-4"><x-adminlte-input name="technologies" label="Tecnologias" :value="old('technologies', $project->technologies)"/></div>
            </div>
            <div class="row">
                <div class="col-md-3"><x-adminlte-select name="status" label="Status">
                    @foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(old('status', $project->status) === $k)>{{ $v }}</option>@endforeach
                </x-adminlte-select></div>
                <div class="col-md-3"><x-adminlte-input name="progress" type="number" min="0" max="100" label="Progresso %" :value="old('progress', $project->progress ?? 0)" required/></div>
                <div class="col-md-3"><x-adminlte-input name="deadline" type="date" label="Prazo" :value="old('deadline', optional($project->deadline)->format('Y-m-d'))"/></div>
                <div class="col-md-3"><x-adminlte-input name="budget" type="number" step="0.01" label="Orçamento (R$)" :value="old('budget', $project->budget)"/></div>
            </div>
            <div class="row">
                <div class="col-md-4"><x-adminlte-input name="image" label="Imagem (path)" :value="old('image', $project->image)"/></div>
                <div class="col-md-4"><x-adminlte-input name="sort_order" type="number" min="0" label="Ordem" :value="old('sort_order', $project->sort_order ?? 0)" required/></div>
                <div class="col-md-4 d-flex align-items-end pb-3"><x-adminlte-input-switch name="featured" label="Destaque no site" :checked="old('featured', $project->featured)"/></div>
            </div>
            <x-adminlte-button label="Salvar" theme="primary" type="submit"/>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-link">Cancelar</a>
        </form>
    </x-adminlte-card>
@stop
