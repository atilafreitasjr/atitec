@auth
    <li class="nav-item d-flex align-items-center">
        <form method="POST" action="{{ route('theme.update') }}" class="at-theme-form">
            @csrf
            <div class="form-check form-switch at-theme-switch mb-0">
                <input type="hidden" name="theme" value="dark">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="theme"
                    value="light"
                    id="atitec-theme-switch"
                    data-theme-switch
                    @checked(auth()->user()?->theme === 'light')
                    aria-label="Alternar entre tema escuro e claro"
                >
                <label class="form-check-label" for="atitec-theme-switch" title="Tema claro">
                    <i class="bi bi-sun" aria-hidden="true"></i>
                    <span class="d-none d-xl-inline">Claro</span>
                </label>
            </div>
        </form>
    </li>
@endauth
