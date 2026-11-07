@php
    $navLeft = app('adminlte')->menu('navbar-left');
@endphp
<nav class="app-header {{ config('adminlte.classes_topnav', 'navbar-expand bg-body') }} navbar">
    <div class="{{ config('adminlte.classes_topnav_container', 'container-fluid') }}">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="{{ __('Toggle sidebar') }}">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </a>
            </li>

            <li class="nav-item d-none d-md-block">
                <a href="{{ url('/') }}" class="nav-link">
                    <i class="bi bi-grid-1x2 me-1" aria-hidden="true"></i> {{ __('adminlte.home') }}
                </a>
            </li>

            @if (config('adminlte.sidebar_docs_url'))
                <li class="nav-item d-none d-md-block">
                    <a href="{{ config('adminlte.sidebar_docs_url') }}" class="nav-link" target="_blank" rel="noopener">
                        <i class="bi bi-book me-1" aria-hidden="true"></i> {{ __('adminlte.documentation') }}
                    </a>
                </li>
            @endif

            @foreach ($navLeft as $item)
                <li class="nav-item d-none d-md-block">
                    <a href="{{ $item['href'] ?? '#' }}" class="nav-link">{{ $item['text'] ?? '' }}</a>
                </li>
            @endforeach
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-flex align-items-center me-lg-2">
                <button type="button" data-adminlte-search aria-label="{{ __('adminlte.search') }}"
                        class="adminlte-search-trigger btn btn-sm border rounded-pill d-flex align-items-center gap-2 px-2 px-lg-3 text-body-secondary">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span class="d-none d-lg-inline">{{ __('adminlte.search') }}…</span>
                    <kbd class="d-none d-lg-inline small border rounded px-1 ms-lg-3">⌘K</kbd>
                </button>
            </li>

            @include('adminlte::partials.navbar-messages')
            @include('adminlte::partials.navbar-notifications')
            @include('adminlte::partials.theme-switch')

            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="{{ __('Toggle fullscreen') }}">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none" aria-hidden="true"></i>
                </a>
            </li>

            @if (config('adminlte.color_mode_toggle', false))
                @include('adminlte::partials.color-mode')
            @endif

            @if (config('adminlte.control_sidebar', false))
                <li class="nav-item">
                    <a class="nav-link" href="#" role="button"
                       data-bs-toggle="offcanvas" data-bs-target="#adminlte-control-sidebar"
                       aria-controls="adminlte-control-sidebar" aria-label="{{ __('Toggle settings panel') }}">
                        <i class="bi bi-gear-fill" aria-hidden="true"></i>
                    </a>
                </li>
            @endif

            @if (config('adminlte.usermenu_enabled', true))
                @include('adminlte::partials.usermenu')
            @endif
        </ul>
    </div>
</nav>

@include('adminlte::partials.command-palette')
