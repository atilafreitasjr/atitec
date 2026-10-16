<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Redireciona para a tela de consentimento do Google.
     */
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'O login com Google ainda não está configurado.']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Trata o retorno do Google: autentica quem já existe ou cria um novo usuário cliente.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Não foi possível entrar com o Google. Tente novamente.']);
        }

        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Não foi possível obter seu e-mail do Google.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user && ! $user->active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Esta conta está desativada. Procure o administrador.']);
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Cliente Google'),
                'email' => $email,
                'password' => null,
                'role' => 'cliente',
                'active' => true,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        // Cliente já cadastrado pelo admin (mesmo e-mail) é vinculado automaticamente.
        if (! $user->client_id) {
            $client = Client::where('user_id', $user->id)
                ->orWhere('email', $email)
                ->first();

            if ($client) {
                $client->forceFill(['user_id' => $user->id])->save();
                $user->forceFill(['client_id' => $client->id])->save();
            }
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        // Sem cliente vinculado: completa os dados cadastrais obrigatórios.
        if ($user->role === 'cliente' && ! $user->client_id) {
            return redirect()->route('cadastro.completar');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
