<div @if($hasActiveRuns) wire:poll.3000ms @endif>
    @if(count($selected) > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-4">
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700">{{ count($selected) }} {{ count($selected) === 1 ? 'item' : 'items' }} selected</span>
                <button wire:click="bulkDelete"
                    wire:confirm="Are you sure you want to delete {{ count($selected) }} selected lead source(s)? This cannot be undone."
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700">
                    Delete selected
                </button>
            </div>
        </div>
    @endif
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left w-10 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <span class="sr-only">Select all</span>
                            <input type="checkbox"
                                class="rounded border-gray-300"
                                aria-label="Select all lead sources"
                                @checked($allSelected)
                                wire:click="toggleSelectAllLeadSources({{ json_encode($ids) }})">
                        </th>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selected" value="{{ $source->id }}" class="rounded border-gray-300" aria-label="Select {{ $source->name }}">
                            </td>
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
                                @php
                                    $runStatus = $source->latestRun?->status?->value;
                                @endphp
                                @if($runStatus === 'queued')
                                    <span class="inline-flex items-center gap-1 text-gray-500">Queued…</span>
                                @elseif($runStatus === 'running')
                                    <span class="inline-flex items-center gap-1 text-amber-600">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Running…
                                    </span>
                                @elseif($source->last_run_at)
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
                                @if($runStatus === 'queued' || $runStatus === 'running')
                                    <span class="text-gray-400 cursor-not-allowed">Run now</span>
                                @else
                                    <button wire:click="runNow({{ $source->id }})" class="text-blue-600 hover:text-blue-900">
                                        Run now
                                    </button>
                                @endif
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
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No lead sources. <a href="{{ route('lead-sources.create') }}" class="text-blue-600 hover:underline" wire:navigate>Create your first lead source</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
