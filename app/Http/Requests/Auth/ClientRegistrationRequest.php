<?php

namespace App\Http\Requests\Auth;

use App\Models\Client;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ClientRegistrationRequest extends FormRequest
{
    /** Unidades federativas do Brasil. */
    public const UFS = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
        'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => preg_replace('/\D+/', '', (string) $this->input('cpf')),
            'cnpj' => preg_replace('/\D+/', '', (string) $this->input('cnpj')),
            'zip_code' => preg_replace('/\D+/', '', (string) $this->input('zip_code')),
            'company_zip_code' => preg_replace('/\D+/', '', (string) $this->input('company_zip_code')),
            'has_company' => $this->boolean('has_company'),
            'company_same_address' => $this->boolean('company_same_address'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $hasCompany = $this->boolean('has_company');
        $companyAddressRequired = $hasCompany && ! $this->boolean('company_same_address');

        $rules = [
            'phone' => ['required', 'string', 'max:20'],
            'cpf' => ['required', 'digits:11', new Cpf, Rule::unique('clients', 'cpf')],
            'zip_code' => ['required', 'digits:8'],
            'address' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'size:2', Rule::in(self::UFS)],
            'has_company' => ['boolean'],
            'company' => [Rule::requiredIf($hasCompany), 'nullable', 'string', 'max:160'],
            'cnpj' => [
                Rule::requiredIf($hasCompany), 'nullable', 'digits:14', new Cnpj,
                Rule::unique('clients', 'cnpj'),
            ],
            'company_same_address' => ['boolean'],
            'company_zip_code' => [Rule::requiredIf($companyAddressRequired), 'nullable', 'digits:8'],
            'company_address' => [Rule::requiredIf($companyAddressRequired), 'nullable', 'string', 'max:255'],
            'company_address_number' => [Rule::requiredIf($companyAddressRequired), 'nullable', 'string', 'max:20'],
            'company_address_complement' => ['nullable', 'string', 'max:120'],
            'company_neighborhood' => [Rule::requiredIf($companyAddressRequired), 'nullable', 'string', 'max:120'],
            'company_city' => [Rule::requiredIf($companyAddressRequired), 'nullable', 'string', 'max:120'],
            'company_state' => [
                Rule::requiredIf($companyAddressRequired), 'nullable', 'string', 'size:2', Rule::in(self::UFS),
            ],
        ];

        // Cadastro público (guest) cria também o usuário; no fluxo "completar
        // cadastro" (autenticado, ex.: login Google) apenas os dados do cliente.
        if ($this->user() === null) {
            $rules += [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email'),
                    function (string $attribute, mixed $value, Closure $fail) {
                        // Cliente pré-cadastrado pelo admin (sem login): não permite
                        // reivindicar o cadastro por senha; orienta o Google/atendimento.
                        if (Client::where('email', $value)->exists()) {
                            $fail('Já existe um cadastro de cliente com este e-mail. Se você já é cliente ATITEC, entre com o Google usando este e-mail ou fale com a ATITEC para liberar seu acesso.');
                        }
                    },
                ],
                'password' => ['required', 'confirmed', Password::defaults()],
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'password' => 'senha',
            'phone' => 'telefone',
            'cpf' => 'CPF',
            'zip_code' => 'CEP',
            'address' => 'endereço',
            'address_number' => 'número',
            'address_complement' => 'complemento',
            'neighborhood' => 'bairro',
            'city' => 'cidade',
            'state' => 'UF',
            'company' => 'empresa',
            'cnpj' => 'CNPJ',
            'company_same_address' => 'endereço da empresa',
            'company_zip_code' => 'CEP da empresa',
            'company_address' => 'endereço da empresa',
            'company_address_number' => 'número da empresa',
            'company_address_complement' => 'complemento da empresa',
            'company_neighborhood' => 'bairro da empresa',
            'company_city' => 'cidade da empresa',
            'company_state' => 'UF da empresa',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Já existe uma conta com este e-mail. Faça login para acessar.',
            'cpf.unique' => 'Este CPF já está cadastrado. Faça login para acessar sua conta.',
            'cpf.digits' => 'O CPF deve conter 11 dígitos.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'cnpj.digits' => 'O CNPJ deve conter 14 dígitos.',
            'cnpj.required' => 'Informe o CNPJ da empresa.',
            'company.required' => 'Informe a razão social/nome da empresa.',
            'zip_code.digits' => 'O CEP deve conter 8 dígitos.',
        ];
    }
}
