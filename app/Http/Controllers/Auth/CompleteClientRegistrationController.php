<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ClientRegistrationRequest;
use App\Support\ClientRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompleteClientRegistrationController extends Controller
{
    /**
     * Formulário de completar cadastro (usuários criados via Google sem cliente vinculado).
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->client_id) {
            return redirect()->route('dashboard');
        }

        return view('auth.complete-registration');
    }

    /**
     * Cria o cliente e vincula ao usuário autenticado.
     */
    public function store(ClientRegistrationRequest $request): RedirectResponse
    {
        if ($request->user()->client_id) {
            return redirect()->route('dashboard');
        }

        ClientRegistration::register($request->user(), $request->validated());

        return redirect()->route('cliente.dashboard')
            ->with('success', 'Cadastro completado! Agora você pode acompanhar seus projetos.');
    }
}
