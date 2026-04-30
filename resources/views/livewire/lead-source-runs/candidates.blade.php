<div>
    <div class="mb-4 flex flex-wrap gap-4 items-center">
        <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
        @if(config('discovery.approve_before_import'))
            <button wire:click="approveSelected" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Approve selected
            </button>
            <button wire:click="scoreSelectedForIcp" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed"
                @if($scoringBulk || empty($selected)) disabled @endif>
                @if($scoringBulk)
                    Scoring…
                @else
                    Score selected against ICP
                @endif
            </button>
            <button wire:click="approveAllHighIcpFit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                @if($highIcpFitNewCount === 0) disabled @endif>
                Approve all high ICP fit
                @if($highIcpFitNewCount > 0)
                    ({{ $highIcpFitNewCount }})
                @endif
            </button>
            <button wire:click="promoteApproved" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Promote approved to accounts
            </button>
        @endif
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if(config('discovery.approve_before_import'))
                            <th scope="col" class="px-6 py-3 text-left w-12 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="sr-only">Select all</span>
                                <input type="checkbox"
                                    class="rounded border-gray-300"
                                    aria-label="Select all candidates on this page"
                                    @checked($allSelected)
                                    wire:click="toggleSelectAllCandidates({{ json_encode($selectableIds) }})">
                            </th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ICP fit</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        @if(config('discovery.approve_before_import'))
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @else
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($candidates as $candidate)
                        <tr wire:key="candidate-{{ $candidate->id }}">
                            @if(config('discovery.approve_before_import'))
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($candidate->status->value === 'new')
                                        <input type="checkbox" wire:model.live="selected" value="{{ $candidate->id }}" class="rounded border-gray-300" aria-label="Select {{ $candidate->domain }}">
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $candidate->domain }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $candidate->url }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $candidate->title ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($candidate->icp_fit)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($candidate->icp_fit)
                                            @case('high') bg-green-100 text-green-800 @break
                                            @case('medium') bg-amber-100 text-amber-800 @break
                                            @case('low') bg-gray-100 text-gray-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst($candidate->icp_fit) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($candidate->status->color())
                                        @case('green') bg-green-100 text-green-800 @break
                                        @case('red') bg-red-100 text-red-800 @break
                                        @case('orange') bg-orange-100 text-orange-800 @break
                                        @case('yellow') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ $candidate->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $candidate->reason ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button type="button" wire:click="showDetails({{ $candidate->id }})" class="text-primary-600 hover:text-primary-900">
                                    Details
                                </button>
                                @if(config('discovery.approve_before_import') && $candidate->status->value === 'new')
                                    <button wire:click="approve({{ $candidate->id }})" class="text-green-600 hover:text-green-900">Approve</button>
                                    <button wire:click="reject({{ $candidate->id }})" class="text-red-600 hover:text-red-900">Reject</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ config('discovery.approve_before_import') ? 8 : 7 }}" class="px-6 py-4 text-center text-gray-500">No candidates for this run.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($detailsCandidate)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog" aria-labelledby="candidate-details-title">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeDetails" aria-hidden="true"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 id="candidate-details-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Candidate details</h2>
                    </div>
                    <div class="px-6 py-4 space-y-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Domain</span>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $detailsCandidate->domain }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">URL</span>
                            <p class="mt-1">
                                <a href="{{ $detailsCandidate->url }}" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:text-primary-900 break-all">{{ $detailsCandidate->url }}</a>
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Title</span>
                            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $detailsCandidate->title ?? '—' }}</p>
                        </div>
                        @if($detailsCandidate->snippet)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Snippet</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $detailsCandidate->snippet }}</p>
                            </div>
                        @endif
                        @if($detailsCandidate->query)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Found by query</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ $detailsCandidate->query }}</p>
                            </div>
                        @endif
                        @if($detailsCandidate->position !== null)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Rank</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">#{{ $detailsCandidate->position }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">ICP fit</span>
                            <p class="mt-1">
                                @if($detailsCandidate->icp_fit)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @switch($detailsCandidate->icp_fit)
                                            @case('high') bg-green-100 text-green-800 @break
                                            @case('medium') bg-amber-100 text-amber-800 @break
                                            @case('low') bg-gray-100 text-gray-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst($detailsCandidate->icp_fit) }}
                                    </span>
                                    @if($detailsCandidate->icp_fit_reason)
                                        <span class="block mt-1 text-gray-600 dark:text-gray-400">{{ $detailsCandidate->icp_fit_reason }}</span>
                                    @endif
                                @else
                                    @if($scoringCandidateId === $detailsCandidate->id)
                                        <span class="text-gray-500">Scoring…</span>
                                    @else
                                        <button type="button" wire:click="scoreCandidateForIcp({{ $detailsCandidate->id }})" class="inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-primary-700">
                                            Score against ICP
                                        </button>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">Status</span>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($detailsCandidate->status->color())
                                        @case('green') bg-green-100 text-green-800 @break
                                        @case('red') bg-red-100 text-red-800 @break
                                        @case('orange') bg-orange-100 text-orange-800 @break
                                        @case('yellow') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ $detailsCandidate->status->label() }}
                                </span>
                            </p>
                        </div>
                        @if($detailsCandidate->reason)
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Reason</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ $detailsCandidate->reason }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-2">
                        @if($detailsCandidate->status->value === 'new')
                            <button type="button" wire:click="approve({{ $detailsCandidate->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Approve
                            </button>
                            <button type="button" wire:click="reject({{ $detailsCandidate->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                Reject
                            </button>
                        @endif
                        <button type="button" wire:click="closeDetails" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
