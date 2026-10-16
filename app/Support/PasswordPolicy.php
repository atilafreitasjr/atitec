<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    /**
     * Regra de validação aplicada em cadastro, reset e troca de senha.
     */
    public static function rule(): Password
    {
        return Password::min(8);
    }

    /**
     * Regras exibidas no formulário (chave => rótulo), na mesma ordem de rule().
     * As chaves são avaliadas em tempo real por resources/js/password.js.
     *
     * @return array<string, string>
     */
    public static function checks(): array
    {
        return [
            'min:8' => 'Pelo menos 8 caracteres',
        ];
    }
}
