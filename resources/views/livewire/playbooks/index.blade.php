<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @if($playbooks->isEmpty())
            <x-empty-state
                title="Create your first outreach playbook"
                description="Playbooks define the angle, LinkedIn DM, and email template the AI uses when drafting outreach for an account. Write one yourself, or have AI draft a starting point from your ICP and value propositions."
                :primary-href="route('playbooks.create')"
                primary-label="Create playbook manually"
                :secondary-href="route('playbooks.generate')"
                secondary-label="Generate with AI"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angle</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($playbooks as $playbook)
                            <tr wire:key="playbook-{{ $playbook->id }}">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $playbook->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $playbook->angle }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($playbook->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <button wire:click="toggleActive({{ $playbook->id }})" class="text-primary-600 hover:text-primary-900">
                                        {{ $playbook->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <a href="{{ route('playbooks.edit', $playbook) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>Edit</a>
                                    <button wire:click="delete({{ $playbook->id }})" wire:confirm="Are you sure you want to delete this playbook?" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
