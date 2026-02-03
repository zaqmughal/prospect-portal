<div>
    <!-- Quick Actions -->
    <div class="flex gap-4 mb-6">
        <a href="{{ route('accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700" wire:navigate>
            Add Account
        </a>
        <a href="{{ route('exports.shortlist') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            Export Shortlist CSV
        </a>
        <a href="{{ route('exports.outreach') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
            Export Outreach Bundle
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm font-medium text-gray-500">Total Accounts</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalAccounts }}</div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm font-medium text-gray-500">Researched Today</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $researchedToday }}</div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm font-medium text-gray-500">AI Spend Today</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($todaySpend, 2) }}</div>
            <div class="text-sm text-gray-500">of ${{ number_format($dailyLimit, 2) }} limit</div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm font-medium text-gray-500">Pipeline</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($pipelineCounts as $stage => $count)
                    @if($count > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst($stage) }}: {{ $count }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hot Leads Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Hot Leads (Top 20 by Score)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pipeline</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Research</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($hotLeads as $account)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $account->name }}</div>
                                <div class="text-sm text-gray-500">{{ $account->domain }}</div>
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('accounts.show', $account) }}" class="text-blue-600 hover:text-blue-900" wire:navigate>View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No accounts yet. <a href="{{ route('accounts.create') }}" class="text-blue-600 hover:underline" wire:navigate>Add your first account</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Research Runs -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Research Runs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pages</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Signals</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">When</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentRuns as $run)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $run->account->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $run->status->color() }}-100 text-{{ $run->status->color() }}-800">
                                    {{ $run->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $run->pages_fetched }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $run->signals_found }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                ${{ number_format($run->total_cost, 4) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $run->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No research runs yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
