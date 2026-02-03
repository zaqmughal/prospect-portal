<div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadence</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leadSources as $source)
                        <tr wire:key="lead-source-{{ $source->id }}">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $source->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $source->type->label() }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $source->cadence->label() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $source->status->value === 'enabled' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $source->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($source->last_run_at)
                                    {{ $source->last_run_at->diffForHumans() }}
                                    @if($source->last_run_status)
                                        <span class="text-gray-400">({{ $source->last_run_status }})</span>
                                    @endif
                                    @if($source->last_run_domains_found !== null)
                                        <span class="text-gray-400">— {{ $source->last_run_domains_found }} found</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button wire:click="runNow({{ $source->id }})" class="text-blue-600 hover:text-blue-900">
                                    Run now
                                </button>
                                <a href="{{ route('lead-sources.edit', $source) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>Edit</a>
                                @if($source->latestRun)
                                    <a href="{{ route('lead-source-runs.candidates', $source->latestRun) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>View candidates</a>
                                @endif
                                <button wire:click="delete({{ $source->id }})" wire:confirm="Are you sure you want to delete this lead source?" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No lead sources. <a href="{{ route('lead-sources.create') }}" class="text-blue-600 hover:underline" wire:navigate>Create your first lead source</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
