<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Minha área — ATITEC</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif
        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl shadow"><p class="text-sm text-gray-500">Meus projetos</p><p class="text-2xl font-bold">{{ $projects->count() }}</p><a href="{{ route('cliente.projetos') }}" class="text-sm text-indigo-600">acompanhar →</a></div>
            <div class="bg-white p-5 rounded-xl shadow"><p class="text-sm text-gray-500">Faturas pendentes</p><p class="text-2xl font-bold">{{ $invoices->where('status', 'pendente')->count() }}</p><a href="{{ route('cliente.financeiro') }}" class="text-sm text-indigo-600">ver cobranças →</a></div>
            <div class="bg-white p-5 rounded-xl shadow"><p class="text-sm text-gray-500">Mensagens</p><p class="text-2xl font-bold">{{ $conversations->count() }}</p><a href="{{ route('cliente.mensagens') }}" class="text-sm text-indigo-600">abrir inbox →</a></div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow">
            <h3 class="font-bold mb-3">Últimos projetos</h3>
            <ul class="divide-y">@forelse($projects as $p)<li class="py-2 flex justify-between"><a class="text-indigo-700" href="{{ route('cliente.projeto', $p->id) }}">{{ $p->title }}</a><span class="text-sm text-gray-500">{{ $p->progress }}% • {{ $p->status }}</span></li>@empty<li class="py-2 text-gray-500">Nenhum projeto vinculado. Fale com a ATITEC.</li>@endforelse</ul>
        </div>
    </div></div>
</x-app-layout>
