<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Organisation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="organizations.create" :dismissible="false" tone="primary" title="Welcome to Prospect Portal">
                <p>
                    Every account, lead source, ICP, and playbook in the platform belongs to an <strong>organisation</strong>. Give yours a name to get started — you can rename it later in Settings, and you can invite teammates once you're set up.
                </p>
            </x-page-intro>

            <livewire:organizations.create />
        </div>
    </div>
</x-app-layout>
