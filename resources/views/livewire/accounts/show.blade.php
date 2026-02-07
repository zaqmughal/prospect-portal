<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $account->name }}</h1>
                <p class="text-gray-500">
                    <a href="{{ $account->url }}" target="_blank" class="text-blue-600 hover:underline">{{ $account->domain }}</a>
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @if($account->sector)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $account->sector }}
                        </span>
                    @endif
                    @if($account->size_band)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $account->size_band }} employees
                        </span>
                    @endif
                    @if($account->location)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $account->location }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-3xl font-bold {{ $account->lead_score >= 60 ? 'text-green-600' : ($account->lead_score >= 40 ? 'text-yellow-600' : 'text-gray-600') }}">
                        {{ $account->lead_score }}
                    </div>
                    <div class="text-xs text-gray-500">Lead Score</div>
                </div>
                <div>
                    <select wire:change="updatePipelineStage($event.target.value)" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($pipelineStages as $stage)
                            <option value="{{ $stage->value }}" {{ $account->pipeline_stage === $stage ? 'selected' : '' }}>
                                {{ $stage->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button wire:click="runResearch" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    {{ $account->research_status->value === 'queued_blocked' ? 'Run research now' : 'Run Research' }}
                </button>
                <a href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" wire:navigate>
                    Edit
                </a>
            </div>
        </div>
        <div class="mt-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $account->research_status->color() }}-100 text-{{ $account->research_status->color() }}-800">
                Research: {{ $account->research_status->label() }}
            </span>
            @if($account->research_status->value === 'queued_blocked' && $account->research_blocked_reason)
                <span class="text-sm text-orange-600 ml-2">{{ $account->research_blocked_reason }}</span>
            @endif
            @if($account->last_researched_at)
                <span class="text-sm text-gray-500 ml-2">Last researched {{ $account->last_researched_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex" aria-label="Tabs">
                @foreach(['overview' => 'Overview', 'signals' => 'Signals', 'brief' => 'Brief', 'outreach' => 'Outreach', 'history' => 'History'] as $tab => $label)
                    <button wire:click="setTab('{{ $tab }}')"
                        class="py-4 px-6 text-sm font-medium border-b-2 {{ $activeTab === $tab ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            @if($activeTab === 'overview')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Score Breakdown</h3>
                        <p class="text-sm text-gray-500 mb-4">Lead score is out of 100. It combines how well the account fits your ICP, how strong the detected signals are, and how ready the account is for outreach.</p>
                        @if($account->score_breakdown)
                            <dl class="space-y-4">
                                @foreach($account->score_breakdown as $category => $data)
                                    <div>
                                        <div class="flex justify-between items-baseline">
                                            <dt class="text-gray-900 font-medium">{{ ucwords(str_replace('_', ' ', $category)) }}</dt>
                                            <dd class="font-semibold text-gray-700">{{ $data['score'] ?? 0 }} / {{ $data['max'] ?? 0 }}</dd>
                                        </div>
                                        @if($category === 'icp_fit')
                                            <p class="text-sm text-gray-500 mt-0.5">How well this account matches your default ICP: sector, size band, and location (UK gets a small bonus). Based on data extracted from the website during research.</p>
                                        @elseif($category === 'signal_strength')
                                            <p class="text-sm text-gray-500 mt-0.5">Strength of detected signals (content, UX, tech, or opportunity issues). High-severity signals score more than medium or low. More relevant signals mean a stronger fit for outreach.</p>
                                        @elseif($category === 'reachability')
                                            <p class="text-sm text-gray-500 mt-0.5">How ready the account is for outreach: has a website, research completed successfully, and a brief was generated so you can personalise messages.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-gray-500">Run research to generate score breakdown</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <p class="text-gray-600">{{ $account->notes ?: 'No notes' }}</p>
                    </div>
                </div>
            @elseif($activeTab === 'signals')
                <div class="space-y-4">
                    @forelse($account->signalEvents as $signal)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $signal->severity->color() }}-100 text-{{ $signal->severity->color() }}-800 mr-2">
                                        {{ $signal->severity->label() }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $signal->type->label() }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-500">+{{ $signal->score_impact }} pts</span>
                            </div>
                            <h4 class="mt-2 font-medium text-gray-900">{{ $signal->title }}</h4>
                            <p class="text-gray-600 text-sm">{{ $signal->description }}</p>
                            @if($signal->evidence_snippet)
                                <blockquote class="mt-2 text-sm text-gray-500 italic border-l-2 border-gray-300 pl-3">
                                    "{{ $signal->evidence_snippet }}"
                                </blockquote>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No signals detected yet. Run research to detect signals.</p>
                    @endforelse
                </div>
            @elseif($activeTab === 'brief')
                @if($account->latestBrief)
                    <div class="max-w-none [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:mt-6 [&_h2]:mb-2 [&_h2]:text-gray-900 [&_h2]:border-b [&_h2]:border-gray-200 [&_h2]:pb-1 [&_h2:first-child]:mt-0 [&_h3]:text-lg [&_h3]:font-medium [&_h3]:mt-4 [&_h3]:mb-1 [&_h3]:text-gray-800 [&_p]:my-2 [&_ul]:my-2 [&_li]:my-0.5">
                        {!! \Illuminate\Support\Str::markdown($account->latestBrief->content_md) !!}
                    </div>
                @else
                    <p class="text-gray-500">No brief generated yet. Run research to generate a brief.</p>
                @endif
            @elseif($activeTab === 'outreach')
                <div class="space-y-4">
                    @forelse($account->outreachAssets as $asset)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $asset->channel->value === 'linkedin' ? 'blue' : 'gray' }}-100 text-{{ $asset->channel->value === 'linkedin' ? 'blue' : 'gray' }}-800">
                                    {{ $asset->channel->label() }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $asset->status->color() }}-100 text-{{ $asset->status->color() }}-800">
                                    {{ $asset->status->label() }}
                                </span>
                            </div>
                            <pre class="whitespace-pre-wrap text-sm text-gray-800 bg-gray-50 p-3 rounded">{{ $asset->content }}</pre>
                            <div class="mt-2 flex gap-2">
                                <button onclick="navigator.clipboard.writeText('{{ addslashes($asset->content) }}'); alert('Copied!')" class="text-sm text-blue-600 hover:text-blue-800">
                                    Copy to Clipboard
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No outreach assets generated yet. Run research to generate outreach messages.</p>
                    @endforelse
                </div>
            @elseif($activeTab === 'history')
                <div class="space-y-4">
                    @forelse($account->researchRuns as $run)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $run->status->color() }}-100 text-{{ $run->status->color() }}-800">
                                        {{ $run->status->label() }}
                                    </span>
                                    <span class="text-sm text-gray-500 ml-2">{{ $run->created_at->format('M j, Y g:ia') }}</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $run->pages_fetched }} pages, {{ $run->signals_found }} signals, ${{ number_format($run->total_cost, 4) }}
                                </div>
                            </div>
                            @if($run->error_message)
                                <p class="mt-2 text-sm text-red-600">{{ $run->error_message }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No research history yet.</p>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
