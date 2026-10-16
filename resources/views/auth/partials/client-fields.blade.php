@use('App\Http\Requests\Auth\ClientRegistrationRequest')

<div x-data="{
    hasCompany: {{ old('has_company') ? 'true' : 'false' }},
    sameCompanyAddress: {{ old('has_company') && ! old('company_same_address') ? 'false' : 'true' }},
}">
    <x-form-section title="Contato e documento">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
            <div>
                <x-input-label for="phone" value="Telefone / WhatsApp" />
                <x-text-input id="phone" name="phone" type="tel" class="block mt-1 w-full"
                              :value="old('phone')" required autocomplete="tel" placeholder="(41) 99999-9999"
                              inputmode="tel" maxlength="15" data-mask="phone" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="cpf" value="CPF" />
                <x-text-input id="cpf" name="cpf" inputmode="numeric" maxlength="14" class="block mt-1 w-full"
                              :value="old('cpf')" required placeholder="000.000.000-00" data-mask="cpf" />
                <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
            </div>
        </div>
    </x-form-section>

    <x-form-section title="Endereço" class="mt-8">
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 mt-3" data-cep-target>
            <div class="sm:col-span-2">
                <x-input-label for="zip_code" value="CEP" />
                <x-text-input id="zip_code" name="zip_code" inputmode="numeric" maxlength="9" class="block mt-1 w-full"
                              :value="old('zip_code')" required placeholder="00000-000" data-mask="cep" data-cep-lookup />
                <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500 empty:hidden" data-cep-status></p>
            </div>

            <div class="sm:col-span-3">
                <x-input-label for="address" value="Endereço (rua)" />
                <x-text-input id="address" name="address" class="block mt-1 w-full"
                              :value="old('address')" required autocomplete="address-line1" data-cep-field="logradouro" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <div class="sm:col-span-1">
                <x-input-label for="address_number" value="Número" />
                <x-text-input id="address_number" name="address_number" class="block mt-1 w-full"
                              :value="old('address_number')" required />
                <x-input-error :messages="$errors->get('address_number')" class="mt-2" />
            </div>

            <div class="sm:col-span-3">
                <x-input-label for="address_complement" value="Complemento" />
                <x-text-input id="address_complement" name="address_complement" class="block mt-1 w-full"
                              :value="old('address_complement')" />
                <x-input-error :messages="$errors->get('address_complement')" class="mt-2" />
            </div>

            <div class="sm:col-span-3">
                <x-input-label for="neighborhood" value="Bairro" />
                <x-text-input id="neighborhood" name="neighborhood" class="block mt-1 w-full"
                              :value="old('neighborhood')" required data-cep-field="bairro" />
                <x-input-error :messages="$errors->get('neighborhood')" class="mt-2" />
            </div>

            <div class="sm:col-span-4">
                <x-input-label for="city" value="Cidade" />
                <x-text-input id="city" name="city" class="block mt-1 w-full"
                              :value="old('city')" required autocomplete="address-level2" data-cep-field="localidade" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="state" value="UF" />
                <select id="state" name="state" required data-cep-field="uf"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">—</option>
                    @foreach (ClientRegistrationRequest::UFS as $uf)
                        <option value="{{ $uf }}" @selected(old('state') === $uf)>{{ $uf }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>
        </div>
    </x-form-section>

    <div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <label class="inline-flex items-center gap-2 font-medium text-gray-700 cursor-pointer">
            <input type="checkbox" name="has_company" value="1" x-model="hasCompany"
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            Sou vinculado(a) a uma empresa
        </label>
        <p class="text-xs text-gray-500 mt-1 ms-6">Marque se o contrato/projeto é feito em nome de uma empresa (CNPJ).</p>

        <div x-show="hasCompany" x-cloak class="mt-4 space-y-4">
            <x-form-section title="Dados da empresa">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                    <div>
                        <x-input-label for="company" value="Razão social / nome da empresa" />
                        <x-text-input id="company" name="company" class="block mt-1 w-full"
                                      :value="old('company')" x-bind:required="hasCompany" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="cnpj" value="CNPJ" />
                        <x-text-input id="cnpj" name="cnpj" inputmode="numeric" maxlength="18" class="block mt-1 w-full"
                                      :value="old('cnpj')" placeholder="00.000.000/0000-00" x-bind:required="hasCompany" data-mask="cnpj" />
                        <x-input-error :messages="$errors->get('cnpj')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="company_same_address" value="1" x-model="sameCompanyAddress"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        O endereço da empresa é o mesmo do meu endereço
                    </label>
                </div>
            </x-form-section>

            <x-form-section title="Endereço da empresa" x-show="! sameCompanyAddress" x-cloak data-cep-target>
                <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 mt-3">
                    <div class="sm:col-span-2">
                        <x-input-label for="company_zip_code" value="CEP da empresa" />
                        <x-text-input id="company_zip_code" name="company_zip_code" inputmode="numeric" maxlength="9" class="block mt-1 w-full"
                                      :value="old('company_zip_code')" placeholder="00000-000" data-mask="cep" data-cep-lookup
                                      x-bind:required="! sameCompanyAddress" />
                        <x-input-error :messages="$errors->get('company_zip_code')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500 empty:hidden" data-cep-status></p>
                    </div>

                    <div class="sm:col-span-3">
                        <x-input-label for="company_address" value="Endereço (rua)" />
                        <x-text-input id="company_address" name="company_address" class="block mt-1 w-full"
                                      :value="old('company_address')" data-cep-field="logradouro"
                                      x-bind:required="! sameCompanyAddress" />
                        <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-1">
                        <x-input-label for="company_address_number" value="Número" />
                        <x-text-input id="company_address_number" name="company_address_number" class="block mt-1 w-full"
                                      :value="old('company_address_number')" x-bind:required="! sameCompanyAddress" />
                        <x-input-error :messages="$errors->get('company_address_number')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-3">
                        <x-input-label for="company_address_complement" value="Complemento" />
                        <x-text-input id="company_address_complement" name="company_address_complement" class="block mt-1 w-full"
                                      :value="old('company_address_complement')" />
                        <x-input-error :messages="$errors->get('company_address_complement')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-3">
                        <x-input-label for="company_neighborhood" value="Bairro" />
                        <x-text-input id="company_neighborhood" name="company_neighborhood" class="block mt-1 w-full"
                                      :value="old('company_neighborhood')" data-cep-field="bairro"
                                      x-bind:required="! sameCompanyAddress" />
                        <x-input-error :messages="$errors->get('company_neighborhood')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-4">
                        <x-input-label for="company_city" value="Cidade" />
                        <x-text-input id="company_city" name="company_city" class="block mt-1 w-full"
                                      :value="old('company_city')" data-cep-field="localidade"
                                      x-bind:required="! sameCompanyAddress" />
                        <x-input-error :messages="$errors->get('company_city')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="company_state" value="UF" />
                        <select id="company_state" name="company_state" data-cep-field="uf" x-bind:required="! sameCompanyAddress"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">—</option>
                            @foreach (ClientRegistrationRequest::UFS as $uf)
                                <option value="{{ $uf }}" @selected(old('company_state') === $uf)>{{ $uf }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('company_state')" class="mt-2" />
                    </div>
                </div>
            </x-form-section>
        </div>
    </div>
</div>
