<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lead Sources') }}
            </h2>
            <a href="{{ route('lead-sources.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add Lead Source
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="lead-sources.index" title="Discover new prospects automatically">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </x-slot:icon>
                <p>
                    Lead sources are reusable discovery configurations — typically a set of search queries — that the platform runs on a schedule (or on demand) to find new prospect domains. Each run produces <strong>candidates</strong> that you review and approve before they become accounts. We deduplicate against your existing pipeline so you won't see the same domain twice.
                </p>
            </x-page-intro>

            <livewire:lead-sources.index />
        </div>
    </div>
</x-app-layout>
