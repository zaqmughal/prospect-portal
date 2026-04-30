<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="accounts.show" title="What you're looking at">
                <p>
                    The <strong>lead score</strong> combines ICP fit, the strength of detected signals, and reachability into a single 0–85 number — higher means a hotter prospect. The <strong>brief</strong> summarises what the AI learnt from the company's site; <strong>signals</strong> are specific findings that influenced the score. The <strong>outreach</strong> section contains AI-drafted email and LinkedIn copy you can copy/paste or refine.
                </p>
                <p class="mt-2">
                    Re-run research at any time using the button below to refresh the brief, signals, and outreach with the latest content from the prospect's site.
                </p>
            </x-page-intro>

            <livewire:accounts.show :account-id="request()->route('account')" />
        </div>
    </div>
</x-app-layout>
