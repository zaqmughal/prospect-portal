@props([
    'id' => '',
    'title' => '',
    'icon' => null,
    'dismissible' => true,
    'tone' => 'primary',
])

@php
    // Persist the dismissed state per-browser via localStorage. The key is
    // namespaced with the page id so each intro is independent. We tolerate a
    // missing id by skipping persistence, which keeps the component usable
    // even when a developer forgets to provide one.
    $storageKey = $id !== '' ? 'pp.page-intro.dismissed.'.$id : '';

    $tones = [
        'primary' => [
            'wrap' => 'bg-primary-50 border-primary-200 text-primary-900',
            'icon' => 'text-primary-600',
            'title' => 'text-primary-900',
            'body' => 'text-primary-800',
            'dismiss' => 'text-primary-700 hover:text-primary-900 focus:ring-primary-500',
        ],
        'amber' => [
            'wrap' => 'bg-amber-50 border-amber-200 text-amber-900',
            'icon' => 'text-amber-600',
            'title' => 'text-amber-900',
            'body' => 'text-amber-800',
            'dismiss' => 'text-amber-700 hover:text-amber-900 focus:ring-amber-500',
        ],
        'gray' => [
            'wrap' => 'bg-gray-50 border-gray-200 text-gray-900',
            'icon' => 'text-gray-600',
            'title' => 'text-gray-900',
            'body' => 'text-gray-700',
            'dismiss' => 'text-gray-600 hover:text-gray-900 focus:ring-gray-500',
        ],
    ];

    $palette = $tones[$tone] ?? $tones['primary'];
@endphp

<aside
    @if($dismissible && $storageKey !== '')
        x-data="{ visible: ! window.localStorage.getItem({{ Js::from($storageKey) }}) }"
        x-show="visible"
        x-cloak
    @endif
    role="note"
    {{ $attributes->merge(['class' => 'mb-6 rounded-xl border p-4 sm:p-5 ' . $palette['wrap']]) }}
>
    <div class="flex items-start gap-3">
        @if($icon)
            <div class="shrink-0 mt-0.5 {{ $palette['icon'] }}">
                {!! $icon !!}
            </div>
        @endif

        <div class="flex-1 min-w-0">
            @if($title !== '')
                <h2 class="text-sm font-semibold {{ $palette['title'] }}">{{ $title }}</h2>
            @endif

            <div class="text-sm {{ $palette['body'] }} {{ $title !== '' ? 'mt-1' : '' }}">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible && $storageKey !== '')
            <button
                type="button"
                x-on:click="window.localStorage.setItem({{ Js::from($storageKey) }}, '1'); visible = false"
                class="shrink-0 -mr-1 -mt-1 p-1 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $palette['dismiss'] }}"
                aria-label="Dismiss this introduction"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</aside>
