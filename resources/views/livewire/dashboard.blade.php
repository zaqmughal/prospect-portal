<div>
    @if (request()->query('checkout') === 'success')
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm text-green-800 font-medium">Your subscription has been activated successfully. Welcome aboard!</p>
        </div>
    @elseif (request()->query('checkout') === 'cancelled')
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
            <p class="text-sm text-amber-800 font-medium">Payment was cancelled. You're on the free plan — you can upgrade anytime from <a href="{{ route('settings.billing') }}" class="underline hover:no-underline" wire:navigate>Settings &rarr; Billing</a>.</p>
        </div>
    @endif

    @if($showOnboarding)
        <section
            aria-labelledby="onboarding-heading"
            class="mb-6 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden"
        >
            <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between gap-4">
                <div>
                    <h2 id="onboarding-heading" class="text-lg font-semibold text-gray-900">
                        Get started with Prospect Portal
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        A few quick steps to get from zero to your first researched lead.
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="dismissOnboarding"
                    wire:confirm="Hide the Get started checklist? You can reopen it from this dashboard later."
                    class="text-sm text-gray-500 hover:text-gray-700 underline underline-offset-2"
                >
                    Hide
                </button>
            </div>

            <div class="px-6 pt-4">
                <div class="flex items-center justify-between text-xs font-medium text-gray-600 mb-2">
                    <span>{{ $onboardingProgress }}% complete</span>
                    <span>{{ collect($onboardingSteps)->where('done', true)->count() }} of {{ count($onboardingSteps) }} steps</span>
                </div>
                <div
                    class="w-full h-2 bg-gray-100 rounded-full overflow-hidden"
                    role="progressbar"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $onboardingProgress }}"
                    aria-label="Onboarding progress"
                >
                    <div class="h-full bg-primary-600 transition-all duration-300" style="width: {{ $onboardingProgress }}%"></div>
                </div>
            </div>

            <ol class="divide-y divide-gray-100 mt-4">
                @foreach($onboardingSteps as $index => $step)
                    <li class="px-6 py-4 flex items-start gap-4">
                        <div class="shrink-0 mt-0.5">
                            @if($step['done'])
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-700" aria-label="Completed">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-sm font-semibold" aria-hidden="true">
                                    {{ $index + 1 }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold {{ $step['done'] ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                                    {{ $step['label'] }}
                                </h3>
                                @if($step['done'])
                                    <span class="text-xs font-medium text-green-700">Done</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $step['description'] }}</p>
                        </div>
                        @unless($step['done'])
                            <a
                                href="{{ route($step['route']) }}"
                                wire:navigate
                                class="shrink-0 inline-flex items-center px-3 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                {{ $step['cta'] }}
                            </a>
                        @endunless
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <!-- Quick Actions -->
    <div class="flex gap-4 mb-6">
        <a href="{{ route('accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700" wire:navigate>
            Add Account
        </a>
        <a href="{{ route('exports.shortlist') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            Export Shortlist CSV
        </a>
        <a href="{{ route('exports.outreach') }}" class="inline-flex items-center px-4 py-2 bg-secondary-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-secondary-600">
            Export Outreach Bundle
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="text-sm font-medium text-gray-500">Total Accounts</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalAccounts }}</div>
        </div>
        <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="text-sm font-medium text-gray-500">Researched Today</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $researchedToday }}</div>
        </div>
        <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="text-sm font-medium text-gray-500">AI Spend Today</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($todaySpend, 2) }}</div>
            <div class="text-sm text-gray-500">of ${{ number_format($dailyLimit, 2) }} limit</div>
        </div>
        <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100 p-6">
            <div class="text-sm font-medium text-gray-500">Pipeline</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($pipelineCounts as $stage => $count)
                    @if($count > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                            {{ ucfirst($stage) }}: {{ $count }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hot Leads Table -->
    <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100 mb-8">
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
                                <a href="{{ route('accounts.show', $account) }}" class="text-primary-600 hover:text-primary-900" wire:navigate>View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No accounts yet. <a href="{{ route('accounts.create') }}" class="text-primary-600 hover:underline" wire:navigate>Add your first account</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Research Runs -->
    <div class="bg-white overflow-hidden rounded-xl shadow-lg border border-gray-100">
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
