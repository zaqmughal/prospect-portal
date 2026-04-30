@props([
    'icon' => null,
    'title' => '',
    'description' => '',
    'primaryHref' => null,
    'primaryLabel' => null,
    'primaryNavigate' => true,
    'secondaryHref' => null,
    'secondaryLabel' => null,
    'secondaryNavigate' => true,
])

<div {{ $attributes->merge(['class' => 'text-center px-6 py-12']) }}>
    @if($icon)
        <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-primary-50 text-primary-600 mb-4">
            {!! $icon !!}
        </div>
    @endif

    @if($title !== '')
        <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
    @endif

    @if($description !== '')
        <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">{{ $description }}</p>
    @endif

    @if($primaryHref || $secondaryHref)
        <div class="mt-6 flex items-center justify-center gap-3 flex-wrap">
            @if($primaryHref && $primaryLabel)
                <a href="{{ $primaryHref }}"
                   @if($primaryNavigate) wire:navigate @endif
                   class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $primaryLabel }}
                </a>
            @endif

            @if($secondaryHref && $secondaryLabel)
                <a href="{{ $secondaryHref }}"
                   @if($secondaryNavigate) wire:navigate @endif
                   class="inline-flex items-center px-4 py-2 bg-white border border-primary-600 rounded-lg font-semibold text-xs text-primary-600 uppercase tracking-widest hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ $secondaryLabel }}
                </a>
            @endif
        </div>
    @endif
</div>
