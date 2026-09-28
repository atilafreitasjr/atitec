<div>
    <div class="card">
        <form wire:submit="save" class="card-body">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" wire:model="name" class="form-control">
                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea wire:model="description" rows="4" class="form-control"></textarea>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Situação</label>
                    <select wire:model="status" class="form-select">
                        <option value="planejamento">Planejamento</option>
                        <option value="andamento">Em Andamento</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data início</label>
                    <input type="date" wire:model="start_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data término</label>
                    <input type="date" wire:model="end_date" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Horas técnicas (HT)</label>
                    <input type="number" step="0.5" min="0" wire:model="hours_estimated" placeholder="ex.: 160" class="form-control">
                    @error('hours_estimated') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Observações</label>
                <textarea wire:model="notes" rows="3" class="form-control"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.projects.deliverables.index', $projectId) }}" class="btn btn-secondary" wire:navigate>Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
