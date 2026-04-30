<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Lead Source') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="lead-sources.create" title="Set up automated prospect discovery">
                <p>
                    Give your lead source a memorable name, choose a discovery type (search-query packs are the most common), and add the queries the platform should run on your behalf. After saving, click <strong>Run now</strong> on the lead sources list to surface candidates immediately, or set a cadence to run automatically.
                </p>
                <p class="mt-2">
                    Need ideas for queries? Click <strong>Generate with AI</strong> on the form to have a starter set drafted from a short business description.
                </p>
            </x-page-intro>

            <livewire:lead-sources.create />
        </div>
    </div>
</x-app-layout>
