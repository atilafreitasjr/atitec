@php
    // Substitui o dropdown de demonstração do AdminLTE por mensagens reais dos clientes.
    $messages = \App\Support\AdminNavbar::messages();
    $messageCount = $messages !== [] ? \App\Support\AdminNavbar::messageCount() : 0;
    $messagesUrl = \Illuminate\Support\Facades\Route::has('admin.conversations.index')
        ? route('admin.conversations.index')
        : '#';
@endphp
@can('conversation.view')
<li class="nav-item dropdown">
    <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-label="Mensagens">
        <i class="bi bi-chat-text" aria-hidden="true"></i>
        @if ($messageCount > 0)
            <span class="navbar-badge badge text-bg-danger">{{ $messageCount }}</span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
        @forelse ($messages as $msg)
            <a href="{{ $msg['url'] }}" class="dropdown-item">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle text-secondary-emphasis me-3" style="width:44px;height:44px">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="dropdown-item-title">
                            {{ $msg['name'] }}
                            <span class="float-end fs-7 text-secondary">{{ $msg['conversation'] }}</span>
                        </h3>
                        <p class="fs-7 mb-0">{{ $msg['text'] }}</p>
                        <p class="fs-7 text-secondary mb-0"><i class="bi bi-clock-fill me-1" aria-hidden="true"></i> {{ $msg['time'] }}</p>
                    </div>
                </div>
            </a>
            <div class="dropdown-divider"></div>
        @empty
            <span class="dropdown-item text-secondary">Nenhuma mensagem nova</span>
            <div class="dropdown-divider"></div>
        @endforelse
        <a href="{{ $messagesUrl }}" class="dropdown-item dropdown-footer">Ver todas as conversas</a>
    </div>
</li>
@endcan
