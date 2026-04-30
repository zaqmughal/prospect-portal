<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create ICP') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="icps.create" title="Build your ICP manually">
                <p>
                    List the sectors that match your offer, weight the four signal categories (<strong>content</strong>, <strong>UX</strong>, <strong>tech</strong>, <strong>opportunity</strong>) by how much each one tells you about fit, and mark this profile as default if it should be used for scoring. Higher weights mean a stronger boost when that signal type is detected on a prospect's site.
                </p>
                <p class="mt-2">
                    Prefer a starting point? <a href="{{ route('icps.generate') }}" class="underline hover:no-underline" wire:navigate>Generate with AI</a> from a short business description and adjust afterwards.
                </p>
            </x-page-intro>

            <livewire:icps.create />
        </div>
    </div>
</x-app-layout>
