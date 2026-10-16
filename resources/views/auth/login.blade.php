<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (app()->environment('local') || filled(config('services.google.client_id')))
        <a href="{{ route('auth.google.redirect') }}"
           class="mb-6 flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
            <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.6 20.5H42V20.5H24v7h11.3C33.7 31.8 29.3 35 24 35c-6.1 0-11-4.9-11-11s4.9-11 11-11c2.8 0 5.4 1.1 7.3 2.8l4.9-4.9C32.6 7.6 28.5 6 24 6 14.1 6 6 14.1 6 24s8.1 18 18 18 18-8.1 18-18c0-1.2-.1-2.3-.4-3.5z"/>
                <path fill="#FF3D00" d="M8.3 14.7l5.7 4.2C15.7 15 19.6 12 24 12c2.8 0 5.4 1.1 7.3 2.8l4.9-4.9C32.6 7.6 28.5 6 24 6 16.9 6 10.8 10 8.3 14.7z"/>
                <path fill="#4CAF50" d="M24 42c5.3 0 9.6-2 13-5.3l-5.9-5c-1.9 1.4-4.3 2.3-7.1 2.3-5.2 0-9.6-3.2-11.3-7.7l-5.7 4.4C9.5 37.5 16.2 42 24 42z"/>
                <path fill="#1976D2" d="M43.6 20.5H42V20.5H24v7h11.3c-.8 2.3-2.3 4.2-4.2 5.6l5.9 5C40.5 34.9 42 30.2 42 24c0-1.2-.1-2.3-.4-3.5z"/>
            </svg>
            Entrar com Google
        </a>

        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-gray-500 uppercase">ou</span></div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Senha" />

            <x-password-input id="password" name="password" class="block mt-1 w-full"
                              required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Lembrar de mim</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Esqueceu a senha?
                </a>
            @endif

            <x-primary-button>
                Entrar
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="text-sm text-gray-600 mt-6 text-center">
                Ainda não tem conta?
                <a class="underline text-gray-800 hover:text-gray-900" href="{{ route('register') }}">Crie sua conta de cliente</a>
            </p>
        @endif
    </form>
</x-guest-layout>
