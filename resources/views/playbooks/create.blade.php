<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Playbook') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-page-intro id="playbooks.create" title="Build an outreach playbook">
                <p>
                    Define the <strong>angle</strong> (the hook for first contact), a short <strong>LinkedIn DM template</strong> (under 300 characters for InMail limits), and a longer <strong>email template</strong>. Use placeholders like <code class="px-1 py-0.5 rounded bg-white/60">{company_name}</code> and <code class="px-1 py-0.5 rounded bg-white/60">{signal}</code> — the AI substitutes them when drafting outreach for each researched account.
                </p>
                <p class="mt-2">
                    Need a starting point? <a href="{{ route('playbooks.generate') }}" class="underline hover:no-underline" wire:navigate>Generate with AI</a> from your business context and ICP.
                </p>
            </x-page-intro>

            <livewire:playbooks.create />
        </div>
    </div>
</x-app-layout>
