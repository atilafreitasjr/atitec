@extends('layouts.site')
@section('title', $servico['titulo'].' — ATITEC')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
    <a href="{{ route('site.home') }}" class="text-sm text-gray-400 hover:text-[#FFFF5D]">← voltar</a>
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase mt-4">Serviço</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">{{ $servico['titulo'] }}</h1>
    <p class="text-lg text-gray-300 mt-3">{{ $servico['subtitulo'] }}</p>
    <p class="text-gray-400 mt-4 max-w-3xl">{{ $servico['descricao'] }}</p>
    <ul class="grid md:grid-cols-2 gap-4 mt-8">
        @foreach($servico['itens'] as $item)
        <li class="bg-white/5 border border-white/10 rounded-xl p-4 flex gap-3"><span class="text-[#FFFF5D] font-bold">✓</span><span class="text-sm">{{ $item }}</span></li>
        @endforeach
    </ul>
    <div class="mt-10 flex flex-wrap gap-3">
        <a href="{{ route('site.orcamento') }}" class="bg-[#FFFF5D] text-black font-semibold px-6 py-3 rounded-lg hover:bg-[#FFD400]">Solicitar proposta</a>
        <a href="{{ route('site.portfolio') }}" class="border border-white/20 px-6 py-3 rounded-lg hover:border-[#FFFF5D] hover:text-[#FFFF5D]">Ver cases</a>
    </div>
</section>
@endsection
