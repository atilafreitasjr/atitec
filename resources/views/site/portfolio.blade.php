@extends('layouts.site')
@section('title', 'Portfólio — ATITEC')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
    <p class="text-[#FFFF5D] font-semibold tracking-widest text-xs uppercase">Portfólio</p>
    <h1 class="font-[Montserrat] font-extrabold text-4xl mt-2">Cases reais, em produção</h1>
    <div class="grid md:grid-cols-3 gap-6 mt-8">
        @foreach($projects as $p)
        <article class="bg-[#1A1D21] border border-white/10 rounded-2xl p-6 hover:border-[#FFFF5D]/60 transition">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400">{{ $p->segment }}</p>
            <h2 class="font-[Montserrat] font-bold text-lg mt-1">{{ $p->title }}</h2>
            <p class="text-sm text-gray-400 mt-2">{{ \Illuminate\Support\Str::limit($p->description, 130) }}</p>
            <p class="text-xs text-gray-500 mt-2">{{ $p->technologies }}</p>
            <div class="flex items-center justify-between mt-4">
                <a href="{{ route('site.case', $p->slug) }}" class="text-[#FFFF5D] text-sm font-semibold hover:underline">Ver case →</a>
                @if($p->url)<a href="{{ $p->url }}" target="_blank" class="text-xs text-gray-400 hover:text-white">visitar ↗</a>@endif
            </div>
        </article>
        @endforeach
    </div>
</section>
@endsection
