<div>
    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body py-2 d-flex gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar entregas..." class="form-control form-control-sm">
            @can('deliverable.create')
                <a href="{{ route('admin.projects.deliverables.create', $projectId) }}" class="btn btn-sm btn-primary text-nowrap" wire:navigate>Nova Entrega</a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nome</th><th>Situação</th><th>HT</th><th>Valor</th><th>Término</th><th></th></tr></thead>
                <tbody>
                    @forelse($deliverables as $deliverable)
                        <tr>
                            <td><a href="{{ route('admin.projects.deliverables.show', [$projectId, $deliverable->id]) }}" wire:navigate>{{ $deliverable->name }}</a></td>
                            <td><span class="badge bg-secondary">{{ ucfirst($deliverable->status) }}</span></td>
                            <td class="small text-muted">{{ $deliverable->hours_estimated ? number_format($deliverable->hours_estimated, 0, ',', '.').' HT' : '—' }}</td>
                            <td class="small">{{ $deliverable->hours_estimated ? 'R$ '.number_format($deliverable->hours_estimated * 100, 2, ',', '.') : '—' }}</td>
                            <td class="small text-muted">{{ $deliverable->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.projects.deliverables.show', [$projectId, $deliverable->id]) }}" class="btn btn-sm btn-outline-info" wire:navigate>Ver</a>
                                @can('deliverable.edit')
                                    <a href="{{ route('admin.projects.deliverables.edit', [$projectId, $deliverable->id]) }}" class="btn btn-sm btn-outline-primary" wire:navigate>Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhuma entrega encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $deliverables->links() }}</div>
    </div>
</div>
