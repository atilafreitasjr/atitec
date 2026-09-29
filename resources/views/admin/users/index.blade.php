@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Usuários</h1>
        @can('user.create')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Novo usuário</a>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    <x-adminlte-card>
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome ou e-mail..." class="form-control form-control-sm"></div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Todos os papéis</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-sm btn-outline-primary">Filtrar</button> <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link">Limpar</a></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nome</th><th>E-mail</th><th>Papel</th><th>Cliente vinculado</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }} @if($user->is(auth()->user()))<span class="badge bg-info">você</span>@endif</td>
                            <td class="small">{{ $user->email }}</td>
                            <td><span class="badge bg-secondary">{{ $user->role }}</span></td>
                            <td class="small">{{ $user->client?->name ?? '—' }}</td>
                            <td>
                                @if($user->active)<span class="badge bg-success">ativo</span>@else<span class="badge bg-danger">inativo</span>@endif
                            </td>
                            <td class="text-end text-nowrap">
                                @can('user.edit')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                @endcan
                                @can('user.delete')
                                    @unless($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Excluir {{ $user->name }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Nenhum usuário encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $users->links() }}</div>
    </x-adminlte-card>
@stop
