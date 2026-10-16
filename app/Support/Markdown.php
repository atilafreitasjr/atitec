<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Renderiza Markdown digitado por usuários (leads, mensagens) com segurança:
 * o HTML bruto é removido e links perigosos (javascript:) são ignorados.
 */
class Markdown
{
    public static function render(?string $text): string
    {
        if (! $text) {
            return '';
        }

        return Str::markdown($text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
