<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->title }}</h2>
            <a href="{{ route('cliente.kanban', $project->id) }}" class="text-sm text-white bg-indigo-600 px-4 py-2 rounded">Acompanhamento detalhado →</a>
        </div>
    </x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white p-5 rounded-xl shadow">
            <p class="text-sm text-gray-600">{{ $project->description }}</p>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-3"><div class="bg-green-600 h-3 rounded-full" style="width:{{ $project->progress }}%"></div></div>
            <p class="text-sm text-gray-500 mt-1">{{ $project->progress }}% • prazo: {{ $project->deadline?->format('d/m/Y') ?? '—' }}</p>
        </div>
        <div class="grid md:grid-cols-4 gap-3">
            @foreach(\App\Models\ProjectTask::STATUSES as $k => $v)
            <div class="bg-white p-4 rounded-xl shadow">
                <h4 class="font-bold text-sm mb-2">{{ $v }}</h4>
                <ul class="space-y-2">@forelse($project->tasks->where('status', $k) as $t)<li class="text-sm bg-gray-50 border rounded p-2">{{ $t->title }}</li>@empty<li class="text-xs text-gray-400">—</li>@endforelse</ul>
            </div>
            @endforeach
        </div>
    </div></div>
</x-app-layout>
