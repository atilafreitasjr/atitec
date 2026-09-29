@php
    // Sem tabela `notifications` não há notificações reais: não exibir dados de demonstração.
    $notifications = \App\Support\AdminNavbar::notifications();
    $notificationCount = \App\Support\AdminNavbar::notificationCount();
    $notificationsUrl = \Illuminate\Support\Facades\Route::has('adminlte.notifications.index')
        ? route('adminlte.notifications.index')
        : '#';
@endphp
@if ($notificationCount > 0 || $notifications !== [])
<li class="nav-item dropdown">
    <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-label="Notificações">
        <i class="bi bi-bell-fill" aria-hidden="true"></i>
        @if ($notificationCount > 0)
            <span class="navbar-badge badge text-bg-warning">{{ $notificationCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        <span class="dropdown-item dropdown-header">{{ $notificationCount }} notificações</span>
        <div class="dropdown-divider"></div>
        @foreach ($notifications as $note)
            <a href="{{ $note['url'] ?? '#' }}" class="dropdown-item">
                <i class="{{ $note['icon'] }} me-2"></i> {{ $note['text'] }}
                <span class="float-end text-secondary fs-7">{{ $note['time'] }}</span>
            </a>
            <div class="dropdown-divider"></div>
        @endforeach
        <a href="{{ $notificationsUrl }}" class="dropdown-item dropdown-footer">Ver todas</a>
    </div>
</li>
@endif
