@php
    $tabs = [
        'general' => ['label' => 'General', 'route' => 'settings'],
        'team' => ['label' => 'Team', 'route' => 'settings.team'],
        'billing' => ['label' => 'Billing', 'route' => 'settings.billing'],
        'usage' => ['label' => 'Usage', 'route' => 'settings.usage'],
    ];
@endphp

<div class="mb-6">
    <nav class="flex space-x-4 border-b border-gray-200" aria-label="Settings">
        @foreach ($tabs as $key => $tab)
            <a href="{{ route($tab['route']) }}"
               wire:navigate
               class="px-3 py-2 text-sm font-medium border-b-2 {{ ($activeTab ?? 'general') === $key
                   ? 'border-primary-500 text-primary-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
