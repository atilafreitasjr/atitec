@extends('layouts.site')
@section('title', $project->title.' — Case ATITEC')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
    <a href="{{ route('site.portfolio') }}" class="text-sm text-gray-400 hover:text-[#FFFF5D]">← portfólio</a>
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase mt-4">{{ $project->segment }}</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">{{ $project->title }}</h1>
    <p class="text-gray-300 mt-4 max-w-3xl">{{ $project->description }}</p>
    @if($project->url)
    <p class="mt-3"><a href="{{ $project->url }}" target="_blank" class="text-[#FFFF5D] hover:underline">{{ $project->url }} ↗</a></p>
    @endif
    <div class="grid md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-red-300">O problema</h3><p class="text-sm text-gray-300 mt-2">{{ $project->problem }}</p></div>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-[#FFFF5D]">A solução ATITEC</h3><p class="text-sm text-gray-300 mt-2">{{ $project->solution }}</p></div>
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6"><h3 class="font-[Montserrat] font-bold text-green-300">Resultados</h3><p class="text-sm text-gray-300 mt-2">{{ $project->results }}</p></div>
    </div>
    <p class="text-xs text-gray-500 mt-6">Tecnologias: {{ $project->technologies }}</p>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('site.orcamento') }}" class="bg-[#FFFF5D] text-black font-semibold px-6 py-3 rounded-lg hover:bg-[#FFD400]">Quero um sistema assim</a>
    </div>
    @if($others->count())
    <h2 class="font-[Montserrat] font-bold text-xl mt-12 mb-4">Outros cases</h2>
    <div class="grid md:grid-cols-3 gap-6">
        @foreach($others as $o)
        <a href="{{ route('site.case', $o->slug) }}" class="bg-[#1A1D21] border border-white/10 rounded-2xl p-5 hover:border-[#FFFF5D]/60"><p class="text-[11px] uppercase tracking-widest text-gray-500">{{ $o->segment }}</p><p class="font-bold mt-1">{{ $o->title }}</p></a>
        @endforeach
    </div>
    @endif
</section>
@endsection
