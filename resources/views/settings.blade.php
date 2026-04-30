<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('settings.partials.tabs', ['activeTab' => 'general'])
            <x-page-intro id="settings.general" title="Organisation settings">
                <p>
                    Manage your organisation's name and URL slug. The slug appears in invitation links and exports — keep it short, lowercase, and hyphen-separated. Only admins can update these settings.
                </p>
            </x-page-intro>

            <livewire:organizations.settings />
        </div>
    </div>
</x-app-layout>
