<div>
    @if (request()->query('checkout') === 'success')
        <div class="mb-6 rounded-md bg-green-50 p-4">
            <p class="text-sm text-green-700">Your subscription has been activated successfully.</p>
        </div>
    @endif

    @if (request()->query('checkout') === 'cancelled')
        <div class="mb-6 rounded-md bg-yellow-50 p-4">
            <p class="text-sm text-yellow-700">Checkout was cancelled. No charges were made.</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    @if (session()->has('billing_error'))
        <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
            <p class="text-sm text-red-700">{{ session('billing_error') }}</p>
        </div>
    @endif

    @if (session()->has('billing_notice'))
        <div class="mb-6 rounded-md bg-blue-50 border border-blue-200 p-4">
            <p class="text-sm text-blue-700">{{ session('billing_notice') }}</p>
        </div>
    @endif

    <!-- Current Plan -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Current Plan</h3>
        <div class="flex items-center gap-3">
            <span class="text-2xl font-bold text-gray-900">{{ $plans[$currentPlan]['label'] ?? ucfirst($currentPlan) }}</span>
            @if ($onTrial)
                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                    Trial
                </span>
            @endif
        </div>

        @if ($hasStripeId && $isOwner)
            <div class="mt-4">
                <button wire:click="redirectToBillingPortal"
                        class="text-sm text-primary-600 hover:text-primary-800 underline">
                    Manage billing &amp; invoices in Stripe
                </button>
            </div>
        @endif
    </div>

    <!-- Plan Comparison -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($plans as $planKey => $plan)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 {{ $currentPlan === $planKey ? 'ring-2 ring-primary-500' : '' }}">
                <div class="mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $plan['label'] }}</h4>
                    <p class="text-3xl font-bold text-gray-900 mt-1">
                        @if ($plan['price_monthly'] === 0)
                            Free
                        @else
                            &pound;{{ $plan['price_monthly'] }}<span class="text-sm font-normal text-gray-500">/month</span>
                        @endif
                    </p>
                </div>

                <ul class="space-y-2 text-sm text-gray-600 mb-6">
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ $plan['accounts_limit'] ?? 'Unlimited' }} accounts
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ $plan['lead_sources_limit'] ?? 'Unlimited' }} lead sources
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ $plan['research_runs_per_month'] ?? 'Unlimited' }} research runs/month
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ $plan['team_members_limit'] }} team {{ $plan['team_members_limit'] === 1 ? 'member' : 'members' }}
                    </li>
                    <li class="flex items-center gap-2">
                        @if ($plan['ai_generated_icps'])
                            <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        @else
                            <svg class="h-4 w-4 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @endif
                        AI-generated ICPs & Playbooks
                    </li>
                </ul>

                @if ($isOwner)
                    @if ($currentPlan === $planKey)
                        <span class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg text-sm font-medium">
                            Current Plan
                        </span>
                    @elseif ($plan['stripe_price_id'])
                        <button wire:click="checkout('{{ $planKey }}')"
                                class="block w-full text-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                            Upgrade to {{ $plan['label'] }}
                        </button>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
</div>
