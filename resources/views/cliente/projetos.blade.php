<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Meus projetos</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-5 rounded-xl shadow">
            <ul class="divide-y">@foreach($projects as $p)<li class="py-3">
                <div class="flex justify-between"><a class="font-bold text-indigo-700" href="{{ route('cliente.projeto', $p->id) }}">{{ $p->title }}</a><span class="text-sm text-gray-500">{{ $p->status }}</span></div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2"><div class="bg-indigo-600 h-2.5 rounded-full" style="width:{{ $p->progress }}%"></div></div>
                <p class="text-sm text-gray-500 mt-1">{{ $p->progress }}% • {{ $p->tasks->where('status', 'concluido')->count() }}/{{ $p->tasks->count() }} tarefas concluídas</p>
            </li>@endforeach</ul>
            <div class="mt-4">{{ $projects->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
