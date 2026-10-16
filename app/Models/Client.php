<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'name', 'company', 'email', 'phone',
        'document', 'cpf', 'cnpj', 'company_same_address',
        'address', 'address_number', 'address_complement',
        'neighborhood', 'city', 'state', 'zip_code',
        'company_address', 'company_address_number', 'company_address_complement',
        'company_neighborhood', 'company_city', 'company_state', 'company_zip_code',
        'notes', 'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'company_same_address' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** CPF formatado (000.000.000-00). */
    public function getFormattedCpfAttribute(): ?string
    {
        return $this->cpf
            ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->cpf)
            : null;
    }

    /** CNPJ formatado (00.000.000/0000-00). */
    public function getFormattedCnpjAttribute(): ?string
    {
        return $this->cnpj
            ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->cnpj)
            : null;
    }

    /** Endereço completo em uma linha (para listagens). */
    public function getFullAddressAttribute(): ?string
    {
        return $this->formatAddress(
            $this->address, $this->address_number, $this->address_complement,
            $this->neighborhood, $this->city, $this->state, $this->zip_code,
        );
    }

    /** Endereço completo da empresa em uma linha. */
    public function getCompanyFullAddressAttribute(): ?string
    {
        return $this->formatAddress(
            $this->company_address, $this->company_address_number, $this->company_address_complement,
            $this->company_neighborhood, $this->company_city, $this->company_state, $this->company_zip_code,
        );
    }

    private function formatAddress(
        ?string $street,
        ?string $number,
        ?string $complement,
        ?string $neighborhood,
        ?string $city,
        ?string $state,
        ?string $zipCode,
    ): ?string {
        $street = trim((string) $street.($number ? ', '.$number : ''));

        $parts = array_filter([
            $street,
            $complement,
            $neighborhood,
            trim((string) $city.($state ? '/'.$state : ''), '/ '),
            $zipCode ? 'CEP '.preg_replace('/(\d{5})(\d{3})/', '$1-$2', $zipCode) : null,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}
