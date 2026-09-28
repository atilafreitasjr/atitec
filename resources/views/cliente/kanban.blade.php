<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Acompanhamento — {{ $project->title }}</h2>
            <a href="{{ route('cliente.projeto', $project->id) }}" class="text-sm text-indigo-600">← voltar ao projeto</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('kanban.board', ['projectId' => $project->id, 'readonly' => true])
        </div>
    </div>
</x-app-layout>
