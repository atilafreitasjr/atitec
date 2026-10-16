@extends('adminlte::page')

@section('title', ($client->exists ? 'Editar' : 'Novo').' cliente')

@section('content_header')<h1 class="mb-0">{{ $client->exists ? 'Editar cliente' : 'Novo cliente' }}</h1>@stop

@section('content')
    @use('App\Http\Requests\Auth\ClientRegistrationRequest')

    @php
        $companySame = old('company_same_address', $client->company_same_address ?? true) == '1';
    @endphp

    <x-adminlte-card>
        <form method="POST" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}">
            @csrf @if($client->exists)@method('PUT')@endif
            <div class="row">
                <div class="col-md-6"><x-adminlte-input name="name" label="Nome" :value="old('name', $client->name)" required/></div>
                <div class="col-md-6"><x-adminlte-input name="company" label="Empresa (razão social)" :value="old('company', $client->company)"/></div>
            </div>
            <div class="row">
                <div class="col-md-4"><x-adminlte-input name="email" type="email" label="E-mail" :value="old('email', $client->email)" required/></div>
                <div class="col-md-4"><x-adminlte-input name="phone" label="Telefone" :value="old('phone', $client->phone)" maxlength="15" data-mask="phone"/></div>
                <div class="col-md-2"><x-adminlte-input name="cpf" label="CPF" :value="old('cpf', $client->formatted_cpf)" placeholder="000.000.000-00" maxlength="14" data-mask="cpf"/></div>
                <div class="col-md-2"><x-adminlte-input name="cnpj" label="CNPJ" :value="old('cnpj', $client->formatted_cnpj)" placeholder="00.000.000/0000-00" maxlength="18" data-mask="cnpj"/></div>
            </div>
            <div data-cep-target>
                <div class="row">
                    <div class="col-md-2">
                        <x-adminlte-input name="zip_code" label="CEP" :value="old('zip_code', $client->zip_code)" placeholder="00000-000" maxlength="9" data-mask="cep" data-cep-lookup>
                            <small class="form-text" data-cep-status></small>
                        </x-adminlte-input>
                    </div>
                    <div class="col-md-6"><x-adminlte-input name="address" label="Endereço (rua)" :value="old('address', $client->address)" data-cep-field="logradouro"/></div>
                    <div class="col-md-1"><x-adminlte-input name="address_number" label="Nº" :value="old('address_number', $client->address_number)"/></div>
                    <div class="col-md-3"><x-adminlte-input name="address_complement" label="Complemento" :value="old('address_complement', $client->address_complement)"/></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><x-adminlte-input name="neighborhood" label="Bairro" :value="old('neighborhood', $client->neighborhood)" data-cep-field="bairro"/></div>
                    <div class="col-md-5"><x-adminlte-input name="city" label="Cidade" :value="old('city', $client->city)" data-cep-field="localidade"/></div>
                    <div class="col-md-3"><x-adminlte-select name="state" label="UF" data-cep-field="uf">
                        <option value="">—</option>
                        @foreach (ClientRegistrationRequest::UFS as $uf)
                            <option value="{{ $uf }}" @selected(old('state', $client->state) === $uf)>{{ $uf }}</option>
                        @endforeach
                    </x-adminlte-select></div>
                </div>
            </div>

            <hr class="mt-0">
            <h5 class="mb-3">Endereço da empresa</h5>
            <input type="hidden" name="company_same_address" value="0">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="company_same_address" value="1" id="company_same_address"
                       data-bs-toggle="collapse" data-bs-target="#company-address-fields" @checked($companySame)>
                <label class="form-check-label" for="company_same_address">O endereço da empresa é o mesmo do cliente</label>
            </div>
            <div id="company-address-fields" class="collapse @if(! $companySame) show @endif" data-cep-target>
                <div class="row">
                    <div class="col-md-2">
                        <x-adminlte-input name="company_zip_code" label="CEP" :value="old('company_zip_code', $client->company_zip_code)" placeholder="00000-000" maxlength="9" data-mask="cep" data-cep-lookup>
                            <small class="form-text" data-cep-status></small>
                        </x-adminlte-input>
                    </div>
                    <div class="col-md-6"><x-adminlte-input name="company_address" label="Endereço (rua)" :value="old('company_address', $client->company_address)" data-cep-field="logradouro"/></div>
                    <div class="col-md-1"><x-adminlte-input name="company_address_number" label="Nº" :value="old('company_address_number', $client->company_address_number)"/></div>
                    <div class="col-md-3"><x-adminlte-input name="company_address_complement" label="Complemento" :value="old('company_address_complement', $client->company_address_complement)"/></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><x-adminlte-input name="company_neighborhood" label="Bairro" :value="old('company_neighborhood', $client->company_neighborhood)" data-cep-field="bairro"/></div>
                    <div class="col-md-5"><x-adminlte-input name="company_city" label="Cidade" :value="old('company_city', $client->company_city)" data-cep-field="localidade"/></div>
                    <div class="col-md-3"><x-adminlte-select name="company_state" label="UF" data-cep-field="uf">
                        <option value="">—</option>
                        @foreach (ClientRegistrationRequest::UFS as $uf)
                            <option value="{{ $uf }}" @selected(old('company_state', $client->company_state) === $uf)>{{ $uf }}</option>
                        @endforeach
                    </x-adminlte-select></div>
                </div>
            </div>

            <x-adminlte-textarea name="notes" label="Observações" rows="2">{{ old('notes', $client->notes) }}</x-adminlte-textarea>
            <div class="row">
                <div class="col-md-6"><x-adminlte-select name="user_id" label="Usuário vinculado (login do cliente)" data-prefill-client-from-user>
                    <option value="">—</option>
                    @foreach($users as $u)<option value="{{ $u->id }}" data-user-name="{{ $u->name }}" data-user-email="{{ $u->email }}" @selected(old('user_id', $client->user_id) == $u->id)>{{ $u->name }} ({{ $u->email }})</option>@endforeach
                </x-adminlte-select></div>
                <div class="col-md-6 d-flex align-items-end pb-3"><x-adminlte-input-switch name="active" label="Ativo" :checked="old('active', $client->active ?? true)"/></div>
            </div>
            <x-adminlte-button label="Salvar" theme="primary" type="submit"/>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-link">Cancelar</a>
        </form>
    </x-adminlte-card>
@stop
