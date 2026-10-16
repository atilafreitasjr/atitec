@props([
    'name',
    'id' => null,
    'rules' => [],
])

@php
    $id = $id ?? $name;
@endphp

<div data-password-wrapper>
    <div class="relative">
        <input
            type="password"
            data-password-field
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pe-10']) }}
        >

        <button type="button" data-password-toggle
                class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                aria-label="Mostrar senha" aria-pressed="false" title="Mostrar senha">
            <svg data-eye-closed class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
            <svg data-eye-open class="hidden d-none h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        </button>
    </div>

    @if (! empty($rules))
        <ul class="hidden d-none mt-2 space-y-1 text-xs" data-password-rules>
            @foreach ($rules as $rule => $label)
                <li data-rule="{{ $rule }}" class="flex items-center gap-1.5 text-gray-400 text-muted">
                    <svg data-rule-icon class="hidden d-none h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <span data-rule-dot class="h-1.5 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                    <span>{{ $label }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
