<x-guest-layout max-width="sm:max-w-3xl">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Complete seu cadastro</h1>
        <p class="text-sm text-gray-600 mt-1">
            Olá, {{ auth()->user()->name }}! Falta pouco: informe seus dados de cliente para acessar
            seus projetos, faturas e mensagens.
        </p>
    </div>

    <form method="POST" action="{{ route('cadastro.completar') }}" class="mt-5">
        @csrf

        <div class="mt-2">
            @include('auth.partials.client-fields')
        </div>

        <div class="flex items-center justify-end mt-8">
            <x-primary-button>
                Salvar e acessar minha área
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
