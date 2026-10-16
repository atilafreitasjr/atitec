@extends('adminlte::page')

@section('title', 'Meus projetos')

@section('content_header')
    <div><p class="text-uppercase small text-warning fw-bold mb-1" style="letter-spacing:.16em">Workspace ATITEC</p><h1 class="mb-0">Meus projetos</h1></div>
@stop

@section('content')
    <x-adminlte-card>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Projeto</th><th>Status</th><th>Entregas</th><th>Progresso</th><th class="text-end">Ações</th></tr></thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('cliente.projeto', $project->id) }}" class="fw-semibold">{{ $project->title }}</a>
                                <small class="d-block text-muted">{{ $project->deadline?->format('d/m/Y') ? 'Prazo: '.$project->deadline->format('d/m/Y') : 'Sem prazo definido' }}</small>
                            </td>
                            <td><span class="badge text-bg-secondary">{{ \App\Models\Project::STATUSES[$project->status] ?? str_replace('_', ' ', ucfirst($project->status)) }}</span></td>
                            <td>{{ $project->deliverables_count }}</td>
                            <td style="min-width:160px"><div class="progress"><div class="progress-bar" style="width:{{ min(100, max(0, $project->progress)) }}%">{{ $project->progress }}%</div></div></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('cliente.projeto', $project->id) }}" class="btn btn-sm btn-outline-warning">Abrir projeto</a>
                                <a href="{{ route('cliente.kanban', $project->id) }}" class="btn btn-sm btn-outline-secondary">Kanban</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Nenhum projeto vinculado ao seu cadastro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $projects->links() }}</div>
    </x-adminlte-card>
@stop
