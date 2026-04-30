<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usage & Statistics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('settings.partials.tabs', ['activeTab' => 'usage'])
            <x-page-intro id="settings.usage" title="Track your activity and AI spend">
                <p>
                    See how much your organisation has researched today, this week, and overall, plus your AI spend against the daily cap on your current plan. Use this to keep an eye on monthly research-run consumption and plan upgrades before you hit limits.
                </p>
            </x-page-intro>

            <livewire:settings />
        </div>
    </div>
</x-app-layout>
