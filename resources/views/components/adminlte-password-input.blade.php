@props([
    'name',
    'id' => null,
    'label' => null,
    'rules' => [],
    'help' => null,
])

@php
    $id = $id ?? $name;
@endphp

<div class="form-group" data-password-wrapper>
    @if ($label)
        <label for="{{ $id }}">{{ $label }}</label>
    @endif

    <div class="input-group">
        <input
            type="password"
            data-password-field
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $attributes->merge(['class' => 'form-control']) }}
        >

        <button type="button" class="btn btn-outline-secondary" data-password-toggle
                aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha">
            <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
            <svg data-eye-open class="hidden d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        </button>
    </div>

    @if ($help)
        <small class="form-text text-muted">{{ $help }}</small>
    @endif

    @if (! empty($rules))
        <ul class="hidden d-none list-unstyled small mb-0 mt-2" data-password-rules>
            @foreach ($rules as $rule => $label)
                <li data-rule="{{ $rule }}" class="text-muted text-gray-400">
                    <svg data-rule-icon class="hidden d-none me-1" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <span data-rule-dot class="me-1" style="display:inline-block;width:.4rem;height:.4rem;border-radius:50%;background:currentColor" aria-hidden="true"></span>
                    <span>{{ $label }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
