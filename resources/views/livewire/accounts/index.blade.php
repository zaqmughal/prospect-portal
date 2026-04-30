<div>
    @if(! $hasAnyAccounts)
        <div class="bg-white shadow-sm sm:rounded-lg">
            <x-empty-state
                title="You haven't added any accounts yet"
                description="Accounts are the prospects you research and pursue. Add a lead source to discover accounts automatically by web search, or create a single account to research right now."
                :primary-href="route('lead-sources.create')"
                primary-label="Create lead source"
                :secondary-href="route('accounts.create')"
                secondary-label="Add account manually"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                    </svg>
                </x-slot:icon>
            </x-empty-state>
        </div>
    @else
    <!-- Filters -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search accounts..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <select wire:model.live="pipelineStage" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Pipeline Stages</option>
                    @foreach($pipelineStages as $stage)
                        <option value="{{ $stage->value }}">{{ $stage->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="researchStatus" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Research Status</option>
                    @foreach($researchStatuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="leadSourceId" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Lead Sources</option>
                    <option value="none">No lead source</option>
                    @foreach($leadSources as $ls)
                        <option value="{{ $ls->id }}">{{ $ls->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="groupBy" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Group by: None</option>
                    <option value="lead_source">Group by: Lead source</option>
                </select>
            </div>
            @if($totalAccountsCount > 0)
                <div>
                    <button type="button" wire:click="selectAllInList" class="text-sm text-primary-600 hover:text-primary-900 hover:underline">
                        Select all {{ $totalAccountsCount }} {{ $totalAccountsCount === 1 ? 'item' : 'items' }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if(count($selected) > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 p-4">
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-700">{{ count($selected) }} {{ count($selected) === 1 ? 'account' : 'accounts' }} selected</span>
                <button wire:click="runSelectedResearch" wire:confirm="Run research on {{ count($selected) }} selected accounts?"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700">
                    Research selected
                </button>
                <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selected) }} selected account(s)? This cannot be undone."
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700">
                    Delete selected
                </button>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left w-10 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <span class="sr-only">Select all</span>
                            <input type="checkbox"
                                class="rounded border-gray-300"
                                aria-label="Select all on this page"
                                @checked($allOnPageSelected)
                                wire:click="toggleSelectAllOnPage({{ json_encode($pageIds) }})">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            Account
                            @if($sortBy === 'name')
                                <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead source</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('sector')">
                            Sector
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('lead_score')">
                            Score
                            @if($sortBy === 'lead_score')
                                <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pipeline
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Research
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if($groupBy === 'lead_source' && count($accountsGrouped) > 0)
                        @foreach($accountsGrouped as $groupName => $groupAccounts)
                            <tr class="bg-gray-50">
                                <td colspan="8" class="px-6 py-2 text-sm font-medium text-gray-700">
                                    {{ $groupName }} ({{ count($groupAccounts) }})
                                </td>
                            </tr>
                            @foreach($groupAccounts as $account)
                                <tr wire:key="account-{{ $account->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" wire:model.live="selected" value="{{ $account->id }}" class="rounded border-gray-300">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('accounts.show', $account) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>
                                            <div class="text-sm font-medium">{{ $account->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $account->domain }}</div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $account->leadSource?->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $account->sector ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->lead_score >= 60 ? 'bg-green-100 text-green-800' : ($account->lead_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $account->lead_score }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $account->pipeline_stage->color() }}-100 text-{{ $account->pipeline_stage->color() }}-800">
                                            {{ $account->pipeline_stage->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $account->research_status->color() }}-100 text-{{ $account->research_status->color() }}-800">
                                            {{ $account->research_status->label() }}
                                        </span>
                                        @if($account->research_status->value === 'queued_blocked' && $account->research_blocked_reason)
                                            <div class="text-xs text-gray-500 mt-1">{{ $account->research_blocked_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                        <button wire:click="runResearch({{ $account->id }})" class="text-green-600 hover:text-green-900" title="{{ $account->research_status->value === 'queued_blocked' ? 'Run research now (override)' : 'Run Research' }}">
                                            {{ $account->research_status->value === 'queued_blocked' ? 'Run research now' : 'Research' }}
                                        </button>
                                        <a href="{{ route('accounts.edit', $account) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>Edit</a>
                                        <button wire:click="delete({{ $account->id }})" wire:confirm="Are you sure you want to delete this account?" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @else
                    @forelse($accounts as $account)
                        <tr wire:key="account-{{ $account->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selected" value="{{ $account->id }}" class="rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('accounts.show', $account) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>
                                    <div class="text-sm font-medium">{{ $account->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $account->domain }}</div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $account->leadSource?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $account->sector ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->lead_score >= 60 ? 'bg-green-100 text-green-800' : ($account->lead_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $account->lead_score }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $account->pipeline_stage->color() }}-100 text-{{ $account->pipeline_stage->color() }}-800">
                                    {{ $account->pipeline_stage->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $account->research_status->color() }}-100 text-{{ $account->research_status->color() }}-800">
                                    {{ $account->research_status->label() }}
                                </span>
                                @if($account->research_status->value === 'queued_blocked' && $account->research_blocked_reason)
                                    <div class="text-xs text-gray-500 mt-1">{{ $account->research_blocked_reason }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button wire:click="runResearch({{ $account->id }})" class="text-green-600 hover:text-green-900" title="{{ $account->research_status->value === 'queued_blocked' ? 'Run research now (override)' : 'Run Research' }}">
                                    {{ $account->research_status->value === 'queued_blocked' ? 'Run research now' : 'Research' }}
                                </button>
                                <a href="{{ route('accounts.edit', $account) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>Edit</a>
                                <button wire:click="delete({{ $account->id }})" wire:confirm="Are you sure you want to delete this account?" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                @if($hasFilters)
                                    No accounts match your current filters. Try clearing the search or filter selections above.
                                @else
                                    No accounts found. <a href="{{ route('accounts.create') }}" class="text-primary-600 hover:underline" wire:navigate>Add your first account</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    @endif
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $accounts->links() }}
        </div>
    </div>
    @endif
</div>
