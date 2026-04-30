<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Discovery candidates') }} — {{ $run->leadSource->name }}
            </h2>
            <a href="{{ route('lead-sources.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50" wire:navigate>
                Back to Lead Sources
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="lead-source-runs.candidates" title="Review discovered candidates">
                <p>
                    Each row is a domain the lead source uncovered. Use the <strong>ICP fit</strong> badge as a quick filter — high-fit candidates closely match your ICP sectors and signals — then approve the ones you want to research. Approved candidates become accounts in your pipeline; rejected ones won't be re-surfaced by future runs of this source.
                </p>
                <p class="mt-2">
                    <strong>Tip:</strong> Use <em>Approve all high ICP fit</em> to fast-track the strongest matches in one click.
                </p>
            </x-page-intro>

            <livewire:lead-source-runs.candidates :run="$run" />
        </div>
    </div>
</x-app-layout>
