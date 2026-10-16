@use('App\Support\PasswordPolicy')

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Alterar senha
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Use uma senha longa e aleatória para manter sua conta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        @if (auth()->user()->password)
            <div>
                <x-input-label for="update_password_current_password" value="Senha atual" />
                <x-password-input id="update_password_current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>
        @else
            <div class="rounded-lg border border-[#e1e100] bg-[#ffff5d]/20 px-4 py-3 text-sm text-gray-700">
                Você entra com o Google e ainda não tem senha. Defina uma senha abaixo para também poder
                entrar digitando e-mail e senha, sem depender do Google.
            </div>
        @endif

        <div>
            <x-input-label for="update_password_password" value="Nova senha" />
            <x-password-input id="update_password_password" name="password" class="mt-1 block w-full"
                              autocomplete="new-password" :rules="PasswordPolicy::checks()" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nova senha" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Senha atualizada.</p>
            @endif
        </div>
    </form>
</section>
