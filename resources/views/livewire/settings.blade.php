<div class="space-y-6">
    <!-- API Configuration -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">API Configuration</h3>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">OpenAI API Key</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($apiKeyConfigured)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Configured
                        </span>
                        <span class="text-gray-500 ml-2">••••••••</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Not Configured
                        </span>
                        <p class="mt-1 text-gray-500">Set OPENAI_API_KEY in your .env file</p>
                    @endif
                </dd>
            </div>
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Models</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <div>Fast: <code class="bg-gray-100 px-1 rounded">{{ config('services.openai.model_fast', 'gpt-4o-mini') }}</code></div>
                    <div>Quality: <code class="bg-gray-100 px-1 rounded">{{ config('services.openai.model_quality', 'gpt-4o') }}</code></div>
                </dd>
            </div>
        </dl>
    </div>

    <!-- Usage Limits -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Limits</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- AI Spend -->
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-500">AI Spend Today</span>
                    <span class="text-sm text-gray-500">${{ number_format($todaySpend, 2) }} / ${{ number_format($dailyLimit, 2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, ($todaySpend / $dailyLimit) * 100) }}%"></div>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Remaining: ${{ number_format($remainingBudget, 2) }}
                </p>
            </div>

            <!-- Research Runs -->
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-500">Research Runs Today</span>
                    <span class="text-sm text-gray-500">{{ $researchToday }} / {{ $researchLimit }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ min(100, ($researchToday / $researchLimit) * 100) }}%"></div>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Remaining: {{ $researchLimit - $researchToday }} runs
                </p>
            </div>
        </div>
    </div>

    <!-- Usage Stats -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h3>

        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Research Runs This Week</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $weeklyRuns }}</dd>
            </div>
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">AI Cost This Week</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($weeklyCost, 2) }}</dd>
            </div>
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Total AI Runs</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalAiRuns }}</dd>
            </div>
        </dl>
    </div>

    <!-- Environment Info -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Environment</h3>

        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">App Environment</dt>
                <dd class="font-medium">{{ config('app.env') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Queue Driver</dt>
                <dd class="font-medium">{{ config('queue.default') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Cache Driver</dt>
                <dd class="font-medium">{{ config('cache.default') }}</dd>
            </div>
        </dl>
    </div>
</div>
