<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate ICP with AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="icps.generate" title="Generate an ICP with AI">
                <p>
                    Describe your business and (optionally) your target market. The AI will draft an ICP with relevant sectors, signal weights for the four categories the scoring engine uses, and section weights summing to 100. The result is saved as a regular ICP — review it on the ICP list page and mark it as default when you're happy.
                </p>
                <p class="mt-2 text-xs">
                    AI-generated ICPs require a Starter plan or above. Free-tier organisations can build ICPs manually instead.
                </p>
            </x-page-intro>

            <livewire:icps.generate />
        </div>
    </div>
</x-app-layout>
