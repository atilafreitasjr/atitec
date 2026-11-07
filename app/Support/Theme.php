<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class Theme
{
    public const COOKIE = 'atitec_theme';

    public const LIGHT = 'light';

    public const DARK = 'dark';

    /** Tema efetivo: a preferência salva no usuário vence; visitantes usam sessão/cookie. */
    public static function current(?Request $request = null): string
    {
        $request ??= request();

        if ($user = $request->user()) {
            return $user->theme === self::LIGHT ? self::LIGHT : self::DARK;
        }

        $theme = $request->hasSession() ? $request->session()->get(self::COOKIE) : null;
        $theme ??= $request->cookie(self::COOKIE);

        return $theme === self::LIGHT ? self::LIGHT : self::DARK;
    }

    public static function isLight(?Request $request = null): bool
    {
        return self::current($request) === self::LIGHT;
    }

    /** Persiste a escolha na sessão/cookie e, quando autenticado, no cadastro do usuário. */
    public static function persist(Request $request, string $theme): void
    {
        $request->session()->put(self::COOKIE, $theme);

        Cookie::queue(cookie(
            self::COOKIE,
            $theme,
            60 * 24 * 365,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));

        if ($user = $request->user()) {
            $user->forceFill(['theme' => $theme])->save();
        }
    }
}
