@php
    $livewire = $livewire ?? false;
    $elements = $elements ?? [];
    $scrollTo = $scrollTo ?? 'body';
    $isCursorPaginator = method_exists($paginator, 'getCursorName');
    $pageName = $isCursorPaginator ? $paginator->getCursorName() : $paginator->getPageName();
    $scrollIntoViewJsSnippet = $livewire && $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
        : '';
@endphp

@if ($paginator->hasPages())
    <nav class="at-pagination" aria-label="Paginação dos resultados">
        <p class="at-pagination__summary">
            @if ($paginator->firstItem())
                Mostrando
                <strong>{{ number_format($paginator->firstItem(), 0, ',', '.') }} a {{ number_format($paginator->lastItem(), 0, ',', '.') }}</strong>
            @else
                Mostrando <strong>0 a 0</strong>
            @endif

            @if (method_exists($paginator, 'total'))
                de <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong>
            @endif

            resultados
        </p>

        <ul class="at-pagination__list">
            <li class="at-pagination__item at-pagination__item--direction at-pagination__item--previous" @if ($livewire) wire:key="paginator-{{ $pageName }}-previous" @endif>
                @if ($paginator->onFirstPage())
                    <span class="at-pagination__button" aria-disabled="true" aria-label="Página anterior, indisponível">
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6" /></svg>
                        Anterior
                    </span>
                @elseif ($livewire && $isCursorPaginator)
                    @php($previousCursor = $paginator->previousCursor() ?? $paginator->cursor())
                    <button
                        type="button"
                        class="at-pagination__button"
                        dusk="previousPage"
                        wire:click="setPage('{{ $previousCursor?->encode() }}', '{{ $pageName }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        aria-label="Página anterior"
                    >
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6" /></svg>
                        Anterior
                    </button>
                @elseif ($livewire)
                    <button
                        type="button"
                        class="at-pagination__button"
                        dusk="previousPage{{ $pageName === 'page' ? '' : '.'.$pageName }}"
                        wire:click="previousPage('{{ $pageName }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        aria-label="Página anterior"
                    >
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6" /></svg>
                        Anterior
                    </button>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="at-pagination__button" rel="prev" aria-label="Página anterior">
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6" /></svg>
                        Anterior
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="at-pagination__item" aria-hidden="true">
                        <span class="at-pagination__ellipsis">{{ $element }}</span>
                    </li>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="at-pagination__item" @if ($livewire) wire:key="paginator-{{ $pageName }}-page{{ $page }}" @endif>
                            @if ($page == $paginator->currentPage())
                                <span class="at-pagination__button" aria-current="page" aria-label="Página {{ $page }}, atual">{{ $page }}</span>
                            @elseif ($livewire)
                                <button
                                    type="button"
                                    class="at-pagination__button"
                                    wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    wire:loading.attr="disabled"
                                    aria-label="Página {{ $page }}"
                                >{{ $page }}</button>
                            @else
                                <a href="{{ $url }}" class="at-pagination__button" aria-label="Página {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="at-pagination__item at-pagination__item--direction at-pagination__item--next" @if ($livewire) wire:key="paginator-{{ $pageName }}-next" @endif>
                @if (! $paginator->hasMorePages())
                    <span class="at-pagination__button" aria-disabled="true" aria-label="Próxima página, indisponível">
                        Próxima
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6" /></svg>
                    </span>
                @elseif ($livewire && $isCursorPaginator)
                    @php($nextCursor = $paginator->nextCursor() ?? $paginator->cursor())
                    <button
                        type="button"
                        class="at-pagination__button"
                        dusk="nextPage"
                        wire:click="setPage('{{ $nextCursor?->encode() }}', '{{ $pageName }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        aria-label="Próxima página"
                    >
                        Próxima
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6" /></svg>
                    </button>
                @elseif ($livewire)
                    <button
                        type="button"
                        class="at-pagination__button"
                        dusk="nextPage{{ $pageName === 'page' ? '' : '.'.$pageName }}"
                        wire:click="nextPage('{{ $pageName }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        aria-label="Próxima página"
                    >
                        Próxima
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6" /></svg>
                    </button>
                @else
                    <a href="{{ $paginator->nextPageUrl() }}" class="at-pagination__button" rel="next" aria-label="Próxima página">
                        Próxima
                        <svg class="at-pagination__arrow" viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6" /></svg>
                    </a>
                @endif
            </li>
        </ul>
    </nav>
@endif
