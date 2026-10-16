<?php

namespace App\Support;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientRegistration
{
    /**
     * Cria o cliente com os dados cadastrais, vincula ao usuário
     * (users.client_id / clients.user_id) e garante o papel "cliente".
     *
     * @param  array<string, mixed>  $data  dados validados por ClientRegistrationRequest
     */
    public static function register(User $user, array $data): Client
    {
        try {
            return static::createFor($user, $data);
        } catch (UniqueConstraintViolationException $exception) {
            $field = match (true) {
                str_contains($exception->getMessage(), 'clients_email_unique') => 'email',
                str_contains($exception->getMessage(), 'clients_cpf_unique') => 'cpf',
                str_contains($exception->getMessage(), 'clients_cnpj_unique') => 'cnpj',
                default => null,
            };

            if ($field === null) {
                throw $exception;
            }

            $messages = [
                'email' => 'Já existe um cadastro de cliente com este e-mail. Se você já é cliente ATITEC, entre com o Google usando este e-mail ou fale com a ATITEC para liberar seu acesso.',
                'cpf' => 'Este CPF já está cadastrado. Faça login para acessar sua conta.',
                'cnpj' => 'Este CNPJ já está cadastrado.',
            ];

            throw ValidationException::withMessages([$field => $messages[$field]]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function createFor(User $user, array $data): Client
    {
        return DB::transaction(function () use ($user, $data) {
            $hasCompany = (bool) ($data['has_company'] ?? false);
            $sameCompanyAddress = $hasCompany && (bool) ($data['company_same_address'] ?? false);

            $client = Client::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'company' => $hasCompany ? ($data['company'] ?? null) : null,
                'email' => $user->email,
                'phone' => $data['phone'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'cnpj' => $hasCompany ? ($data['cnpj'] ?? null) : null,
                'address' => $data['address'] ?? null,
                'address_number' => $data['address_number'] ?? null,
                'address_complement' => $data['address_complement'] ?? null,
                'neighborhood' => $data['neighborhood'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'company_same_address' => $sameCompanyAddress,
                'company_address' => $hasCompany && ! $sameCompanyAddress ? ($data['company_address'] ?? null) : null,
                'company_address_number' => $hasCompany && ! $sameCompanyAddress ? ($data['company_address_number'] ?? null) : null,
                'company_address_complement' => $hasCompany && ! $sameCompanyAddress ? ($data['company_address_complement'] ?? null) : null,
                'company_neighborhood' => $hasCompany && ! $sameCompanyAddress ? ($data['company_neighborhood'] ?? null) : null,
                'company_city' => $hasCompany && ! $sameCompanyAddress ? ($data['company_city'] ?? null) : null,
                'company_state' => $hasCompany && ! $sameCompanyAddress ? ($data['company_state'] ?? null) : null,
                'company_zip_code' => $hasCompany && ! $sameCompanyAddress ? ($data['company_zip_code'] ?? null) : null,
                'active' => true,
            ]);

            $user->forceFill([
                'client_id' => $client->id,
                'role' => 'cliente',
                'active' => true,
            ])->save();

            return $client;
        });
    }
}
