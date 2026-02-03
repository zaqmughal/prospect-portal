<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sectors</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size Bands</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($icps as $icp)
                        <tr wire:key="icp-{{ $icp->id }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $icp->name }}</div>
                                <div class="text-sm text-gray-500">{{ $icp->description }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($icp->sectors)
                                    {{ implode(', ', array_slice($icp->sectors, 0, 3)) }}
                                    @if(count($icp->sectors) > 3)
                                        <span class="text-gray-400">+{{ count($icp->sectors) - 3 }} more</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $icp->size_bands ? implode(', ', $icp->size_bands) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($icp->is_default)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Default
                                    </span>
                                @else
                                    <button wire:click="setDefault({{ $icp->id }})" class="text-sm text-blue-600 hover:text-blue-900">
                                        Set as Default
                                    </button>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a href="{{ route('icps.edit', $icp) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>Edit</a>
                                <button wire:click="delete({{ $icp->id }})" wire:confirm="Are you sure you want to delete this ICP?" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No ICPs found. <a href="{{ route('icps.create') }}" class="text-blue-600 hover:underline" wire:navigate>Create your first ICP</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
