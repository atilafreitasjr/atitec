<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    /**
     * Valida um CNPJ (14 dígitos, com dígitos verificadores).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid((string) $value)) {
            $fail('O campo :attribute não é um CNPJ válido.');
        }
    }

    public static function isValid(string $value): bool
    {
        $cnpj = preg_replace('/\D+/', '', $value);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        foreach ([12, 13] as $position) {
            $sum = 0;
            $weight = $position - 7;

            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $cnpj[$i] * $weight;
                $weight = $weight === 2 ? 9 : $weight - 1;
            }

            $digit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

            if ((int) $cnpj[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
