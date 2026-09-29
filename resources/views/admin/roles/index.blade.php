@extends('adminlte::page')

@section('title', 'Papéis e permissões')

@section('content_header')
    <h1 class="mb-0">Papéis e permissões</h1>
@stop

@section('content')
    @if(session('success'))<x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert>@endif
    @if($errors->any())<x-adminlte-alert theme="danger" title="Atenção">{{ $errors->first() }}</x-adminlte-alert>@endif

    <div class="row">
        <div class="col-lg-9">
            <x-adminlte-card title="Matriz de permissões" theme="primary" icon="bi bi-shield-lock">
                <form method="POST" action="{{ route('admin.roles.bulk') }}">
                    @csrf @method('PUT')
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width:220px">Permissão</th>
                                    @foreach($roles as $role)
                                        <th class="text-center">
                                            {{ $role->name }}
                                            @if($role->name === 'admin')<br><small class="text-muted fw-normal">(todas)</small>@endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($catalog as $module => $actions)
                                    <tr class="table-light"><td colspan="{{ $roles->count() + 1 }}" class="fw-bold text-uppercase small">{{ $module }}</td></tr>
                                    @foreach($actions as $action)
                                        @php $permission = "{$module}.{$action}"; @endphp
                                        <tr>
                                            <td><code class="small">{{ $permission }}</code></td>
                                            @foreach($roles as $role)
                                                @php $isAdminRole = $role->name === 'admin'; @endphp
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           @if($isAdminRole) disabled checked @else name="roles[{{ $role->name }}][]" value="{{ $permission }}" @checked($role->hasPermissionTo($permission)) @endif>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @can('role.edit')
                        <div class="mt-3">
                            <x-adminlte-button label="Salvar permissões" theme="primary" type="submit" icon="bi bi-check"/>
                            <span class="text-muted small ms-2">O papel <strong>admin</strong> sempre mantém todas as permissões.</span>
                        </div>
                    @endcan
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-lg-3">
            <x-adminlte-card title="Novo papel" theme="success" icon="bi bi-plus-circle">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <x-adminlte-input name="name" label="Nome do papel" placeholder="ex.: suporte" required/>
                    <p class="small text-muted">Permissões podem ser marcadas depois na matriz.</p>
                    <x-adminlte-button label="Criar papel" theme="success" type="submit"/>
                </form>
            </x-adminlte-card>

            <x-adminlte-card title="Papéis" theme="secondary" icon="bi bi-people">
                <ul class="list-group list-group-flush">
                    @foreach($roles as $role)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                {{ $role->name }}
                                <small class="text-muted d-block">{{ $role->permissions->count() }} permissão(ões)</small>
                            </span>
                            @if(! in_array($role->name, $coreRoles, true))
                                @can('role.delete')
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Excluir o papel {{ $role->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                @endcan
                            @else
                                <span class="badge bg-light text-muted border">padrão</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-adminlte-card>
        </div>
    </div>
@stop
