<div>
    <!-- Filters -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search accounts..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="pipelineStage" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Pipeline Stages</option>
                    @foreach($pipelineStages as $stage)
                        <option value="{{ $stage->value }}">{{ $stage->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="researchStatus" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Research Status</option>
                    @foreach($researchStatuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            @if(count($selected) > 0)
                <div>
                    <button wire:click="runSelectedResearch" wire:confirm="Run research on {{ count($selected) }} selected accounts?"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700">
                        Research Selected ({{ count($selected) }})
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                            Account
                            @if($sortBy === 'name')
                                <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
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
                    @forelse($accounts as $account)
                        <tr wire:key="account-{{ $account->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selected" value="{{ $account->id }}" class="rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>
                                    <div class="text-sm font-medium">{{ $account->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $account->domain }}</div>
                                </a>
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
                                <a href="{{ route('accounts.edit', $account) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>Edit</a>
                                <button wire:click="delete({{ $account->id }})" wire:confirm="Are you sure you want to delete this account?" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No accounts found. <a href="{{ route('accounts.create') }}" class="text-blue-600 hover:underline" wire:navigate>Add your first account</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $accounts->links() }}
        </div>
    </div>
</div>
