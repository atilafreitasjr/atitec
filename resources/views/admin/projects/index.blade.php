@extends('adminlte::page')

@section('title', 'Projetos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Projetos</h1>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Novo projeto</a>
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    <x-adminlte-card>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Título</th><th>Cliente</th><th>Status</th><th>Progresso</th><th></th></tr></thead>
                <tbody>
                    @forelse($projects as $p)
                    <tr>
                        <td><a href="{{ route('admin.projects.show', $p) }}">{{ $p->title }}</a></td>
                        <td>{{ $p->client?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary">{{ $p->status }}</span></td>
                        <td style="min-width:140px"><div class="progress"><div class="progress-bar" style="width:{{ $p->progress }}%">{{ $p->progress }}%</div></div></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.projects.kanban', $p) }}" class="btn btn-sm btn-outline-success">Kanban</a>
                            <a href="{{ route('admin.projects.edit', $p) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        </td>
                    </tr>
                    @empty<tr><td colspan="5" class="text-center">Nenhum projeto.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $projects->links() }}</div>
    </x-adminlte-card>
@stop
