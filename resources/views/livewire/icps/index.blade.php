<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        @if($icps->isEmpty())
            <x-empty-state
                title="Define your first Ideal Customer Profile"
                description="Your ICP tells the platform which sectors and signals matter to you so that prospect scoring is accurate. You can write one yourself or have AI draft a starting point from a short business description."
                :primary-href="route('icps.create')"
                primary-label="Create ICP manually"
                :secondary-href="route('icps.generate')"
                secondary-label="Generate with AI"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sectors</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($icps as $icp)
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
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($icp->is_default)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Default
                                        </span>
                                    @else
                                        <button wire:click="setDefault({{ $icp->id }})" class="text-sm text-primary-600 hover:text-primary-900">
                                            Set as Default
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <a href="{{ route('icps.edit', $icp) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>Edit</a>
                                    <button wire:click="delete({{ $icp->id }})" wire:confirm="Are you sure you want to delete this ICP?" class="text-red-600 hover:text-red-900">
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
