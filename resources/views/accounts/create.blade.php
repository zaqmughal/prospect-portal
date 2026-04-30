<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="accounts.create" title="Add a single account manually">
                <p>
                    Use this form to add a known prospect by URL — perfect for testing the research flow before you set up automated discovery. We'll normalise the domain, deduplicate against your existing accounts, and queue it ready for research. For bulk discovery from search queries, use a <a href="{{ route('lead-sources.create') }}" class="underline hover:no-underline" wire:navigate>lead source</a> instead.
                </p>
            </x-page-intro>

            <livewire:accounts.create />
        </div>
    </div>
</x-app-layout>
