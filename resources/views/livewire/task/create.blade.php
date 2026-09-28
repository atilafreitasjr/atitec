<div>
    <div class="card">
        <form wire:submit="save" class="card-body">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" wire:model="title" class="form-control">
                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Entrega *</label>
                    <select wire:model="deliverable_id" class="form-select">
                        <option value="">Selecione</option>
                        @foreach($deliverables as $deliverable)
                            <option value="{{ $deliverable->id }}">{{ $deliverable->name }}</option>
                        @endforeach
                    </select>
                    @error('deliverable_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Responsável</label>
                    <select wire:model="user_id" class="form-select">
                        <option value="">Selecione</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fase</label>
                    <select wire:model="phase" class="form-select">
                        <option value="">Selecione</option>
                        <option value="1">Fase 1 — Planejamento e Diagnóstico</option>
                        <option value="2">Fase 2 — Capacitação e Materiais</option>
                        <option value="3">Fase 3 — Acompanhamento e Articulação</option>
                        <option value="4">Fase 4 — Sistematização e Encerramento</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Situação</label>
                    <select wire:model="status" class="form-select">
                        <option value="pendente">Pendente</option>
                        <option value="andamento">Em Andamento</option>
                        <option value="concluido">Concluído</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea wire:model="description" rows="3" class="form-control"></textarea>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prioridade</label>
                    <select wire:model="priority" class="form-select">
                        <option value="baixa">Baixa</option>
                        <option value="media">Média</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Data limite</label>
                    <input type="date" wire:model="due_date" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Valor hora (R$)</label>
                    <input type="number" step="0.01" wire:model="hourly_rate" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Data início</label>
                    <input type="date" wire:model="start_date" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Data fim</label>
                    <input type="date" wire:model="end_date" class="form-control">
                    @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Horas previstas</label>
                    <input type="number" step="0.5" wire:model="hours_estimated" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Horas realizadas</label>
                    <input type="number" step="0.5" wire:model="hours_actual" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.projects.tasks.index', $projectId) }}" class="btn btn-secondary" wire:navigate>Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
