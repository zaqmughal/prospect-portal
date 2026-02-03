<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                    @forelse($playbooks as $playbook)
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
                                <button wire:click="toggleActive({{ $playbook->id }})" class="text-blue-600 hover:text-blue-900">
                                    {{ $playbook->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <a href="{{ route('playbooks.edit', $playbook) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>Edit</a>
                                <button wire:click="delete({{ $playbook->id }})" wire:confirm="Are you sure you want to delete this playbook?" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No playbooks found. <a href="{{ route('playbooks.create') }}" class="text-blue-600 hover:underline" wire:navigate>Create your first playbook</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
