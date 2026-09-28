<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Mensagens</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if(session('success'))<div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>@endif
        @if($broadcasts->count())
        <div class="bg-blue-50 border border-blue-200 p-5 rounded-xl">
            <h3 class="font-bold mb-2">Comunicados gerais</h3>
            @foreach($broadcasts as $b)@foreach($b->messages as $m)<p class="text-sm py-1"><strong>{{ $b->subject }}:</strong> {{ $m->body }}</p>@endforeach@endforeach
        </div>
        @endif
        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-xl shadow">
                <h3 class="font-bold mb-3">Minhas conversas</h3>
                @forelse($conversations as $c)<div class="border rounded p-3 mb-2">
                    <p class="font-medium text-sm">{{ $c->subject }}</p>
                    @foreach($c->messages as $m)<p class="text-sm text-gray-600 mt-1"><strong>{{ $m->user?->name }}:</strong> {{ $m->body }}</p>@endforeach
                </div>@empty<p class="text-sm text-gray-500">Nenhuma conversa ainda.</p>@endforelse
            </div>
            <div class="bg-white p-5 rounded-xl shadow">
                <h3 class="font-bold mb-3">Nova mensagem</h3>
                <form method="POST" action="{{ route('cliente.mensagens.store') }}" class="space-y-3">
                    @csrf
                    <div><label class="text-sm">Conversa existente (opcional)</label><select name="conversation_id" class="w-full border rounded px-3 py-2 text-sm"><option value="">— nova conversa —</option>@foreach($conversations as $c)<option value="{{ $c->id }}">{{ $c->subject }}</option>@endforeach</select></div>
                    <div><label class="text-sm">Assunto (nova conversa)</label><input name="subject" class="w-full border rounded px-3 py-2 text-sm"></div>
                    <div><label class="text-sm">Mensagem*</label><textarea name="body" rows="4" required class="w-full border rounded px-3 py-2 text-sm"></textarea></div>
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded text-sm">Enviar</button>
                </form>
            </div>
        </div>
    </div></div>
</x-app-layout>
