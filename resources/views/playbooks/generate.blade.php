<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Playbook with AI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="playbooks.generate" title="Generate a playbook with AI">
                <p>
                    Describe your business, optionally provide ICP context and your key value propositions, and the AI will draft a complete playbook — angle, LinkedIn DM, email template (with subject line), and a small set of constraints. The result is saved as inactive so you can review and edit before activating.
                </p>
                <p class="mt-2 text-xs">
                    AI-generated playbooks require a Starter plan or above. Free-tier organisations can build playbooks manually instead.
                </p>
            </x-page-intro>

            <livewire:playbooks.generate />
        </div>
    </div>
</x-app-layout>
