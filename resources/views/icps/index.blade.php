<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ideal Customer Profiles') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('icps.generate') }}" class="inline-flex items-center px-4 py-2 bg-white border border-primary-600 rounded-lg font-semibold text-xs text-primary-600 uppercase tracking-widest hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150" wire:navigate>
                    Generate with AI
                </a>
                <a href="{{ route('icps.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150" wire:navigate>
                    Add ICP
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="icps.index" title="Define who you sell to">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                    </svg>
                </x-slot:icon>
                <p>
                    An <strong>Ideal Customer Profile</strong> tells the platform which sectors and signals matter to you. Every account is scored against your default ICP — better fit means a higher lead score, so the right prospects rise to the top. Mark one ICP as default; create additional profiles for distinct segments or campaigns.
                </p>
            </x-page-intro>

            <livewire:icps.index />
        </div>
    </div>
</x-app-layout>
