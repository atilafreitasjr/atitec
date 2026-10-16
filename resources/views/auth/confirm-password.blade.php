<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Esta é uma área segura do sistema. Confirme sua senha para continuar.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Senha" />

            <x-password-input id="password" name="password" class="block mt-1 w-full"
                              required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                Confirmar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
