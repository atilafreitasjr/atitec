<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Contas e cobranças</h2></x-slot>
    <div class="py-8"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-5 rounded-xl shadow overflow-x-auto">
            <table class="w-full text-sm"><thead><tr class="text-left text-gray-500"><th class="py-2">Título</th><th>Tipo</th><th class="text-right">Valor</th><th>Vencimento</th><th>Status</th></tr></thead>
            <tbody>@forelse($invoices as $f)<tr class="border-t">
                <td class="py-2 font-medium">{{ $f->title }}</td><td>{{ $f->type }}</td>
                <td class="text-right">R$ {{ number_format($f->amount, 2, ',', '.') }}</td>
                <td>{{ $f->due_date->format('d/m/Y') }}</td>
                <td><span class="px-2 py-1 rounded text-xs {{ $f->status === 'pago' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $f->status }}</span></td>
            </tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-500">Nenhuma cobrança.</td></tr>@endforelse</tbody></table>
            <div class="mt-4">{{ $invoices->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
