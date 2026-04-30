<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AI-powered prospect research and outreach platform. Discover leads, research accounts, and generate personalised outreach at scale.">

    <title>{{ config('app.name', 'Prospect Portal') }} — AI-Powered Prospect Research</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-white text-gray-900 antialiased">

    <!-- Skip link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary-600 text-white px-4 py-2 rounded-md z-50">
        Skip to main content
    </a>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/90 backdrop-blur-md border-b border-gray-100 z-50" role="navigation" aria-label="Main navigation">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-auto" />
                </div>

                <div class="hidden sm:flex items-center gap-6">
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Features</a>
                    <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">How It Works</a>
                    <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Pricing</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Log In</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                            Get Started Free
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main id="main-content">

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-secondary-50"></div>
            <div class="absolute top-20 right-0 w-96 h-96 bg-primary-100 rounded-full blur-3xl opacity-40"></div>
            <div class="absolute bottom-0 left-10 w-72 h-72 bg-secondary-100 rounded-full blur-3xl opacity-30"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-100 text-primary-700 text-sm font-medium mb-6">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                        AI-Powered Prospecting
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1]">
                        Research prospects.
                        <span class="text-gradient">Write outreach.</span>
                        Close deals.
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Automatically research company websites, detect buying signals, and generate personalised outreach messages — all powered by AI.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-600/20 text-base">
                            Start Free Trial
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </a>
                        <a href="#how-it-works" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-white text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition border border-gray-200 text-base">
                            See How It Works
                        </a>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">No credit card required &middot; Free plan available forever</p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 sm:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Everything you need to prospect smarter</h2>
                    <p class="mt-4 text-lg text-gray-600">From lead discovery to personalised outreach, all in one platform.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Lead Discovery</h3>
                        <p class="text-gray-600">Automatically discover new prospects matching your ideal customer profile using AI-powered search queries.</p>
                    </div>

                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Deep Research</h3>
                        <p class="text-gray-600">AI crawls prospect websites, extracts key information, and detects buying signals so you know exactly when to reach out.</p>
                    </div>

                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-secondary-100 text-secondary-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Outreach</h3>
                        <p class="text-gray-600">Generate personalised LinkedIn DMs and emails tailored to each prospect's specific situation and needs.</p>
                    </div>

                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Lead Scoring</h3>
                        <p class="text-gray-600">Score every prospect against your ICP automatically, so you focus on the accounts most likely to convert.</p>
                    </div>

                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Team Collaboration</h3>
                        <p class="text-gray-600">Invite your team, share prospect pipelines, and collaborate on outreach strategies within your organisation.</p>
                    </div>

                    <div class="p-6 rounded-2xl border border-gray-100 hover:border-primary-200 hover:shadow-lg transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Custom Playbooks</h3>
                        <p class="text-gray-600">Define outreach templates and constraints, then let AI personalise each message while staying on-brand.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-20 sm:py-28 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">How it works</h2>
                    <p class="mt-4 text-lg text-gray-600">Three steps to transform your prospecting workflow.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-primary-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Define your ICP</h3>
                        <p class="text-gray-600">Tell us about your ideal customer — sectors, signals, and what makes a perfect fit. AI can generate this for you on paid plans.</p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-primary-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Discover &amp; Research</h3>
                        <p class="text-gray-600">Set up lead sources to discover prospects automatically. AI researches each company's website and detects buying signals.</p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 rounded-2xl bg-primary-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Outreach at Scale</h3>
                        <p class="text-gray-600">AI generates personalised LinkedIn DMs and emails for each prospect, based on their specific signals and your playbook.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-20 sm:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">Simple, transparent pricing</h2>
                    <p class="mt-4 text-lg text-gray-600">Start free and scale as you grow. No hidden fees.</p>
                </div>

                @php $plans = config('plans'); @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    @foreach ($plans as $planKey => $plan)
                        <div class="relative rounded-2xl border {{ $planKey === 'starter' ? 'border-primary-500 shadow-xl ring-1 ring-primary-500' : 'border-gray-200 shadow-sm' }} bg-white p-8 flex flex-col">
                            @if ($planKey === 'starter')
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                    <span class="inline-flex items-center px-4 py-1 rounded-full bg-primary-600 text-white text-xs font-semibold uppercase tracking-wide">Most Popular</span>
                                </div>
                            @endif

                            <div class="mb-6">
                                <h3 class="text-xl font-bold text-gray-900">{{ $plan['label'] }}</h3>
                                <div class="mt-3">
                                    @if ($plan['price_monthly'] === 0)
                                        <span class="text-4xl font-extrabold text-gray-900">Free</span>
                                    @else
                                        <span class="text-4xl font-extrabold text-gray-900">&pound;{{ $plan['price_monthly'] }}</span>
                                        <span class="text-gray-500 text-base font-medium">/month</span>
                                    @endif
                                </div>
                            </div>

                            <ul class="space-y-3 text-sm text-gray-600 mb-8 flex-1" role="list">
                                <li class="flex items-start gap-2.5">
                                    <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span><strong>{{ $plan['accounts_limit'] ?? 'Unlimited' }}</strong> prospect accounts</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span><strong>{{ $plan['lead_sources_limit'] ?? 'Unlimited' }}</strong> lead {{ ($plan['lead_sources_limit'] ?? 0) === 1 ? 'source' : 'sources' }}</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span><strong>{{ $plan['research_runs_per_month'] ?? 'Unlimited' }}</strong> research runs/month</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    <span><strong>{{ $plan['team_members_limit'] }}</strong> team {{ $plan['team_members_limit'] === 1 ? 'member' : 'members' }}</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    @if ($plan['ai_generated_icps'])
                                        <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    @else
                                        <svg class="h-5 w-5 text-gray-300 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    @endif
                                    <span>AI-generated ICPs &amp; Playbooks</span>
                                </li>
                            </ul>

                            <a href="{{ route('register', ['plan' => $planKey]) }}"
                               class="block w-full text-center px-6 py-3 rounded-xl font-semibold text-sm transition {{ $planKey === 'starter' ? 'bg-primary-600 text-white hover:bg-primary-700 shadow-lg shadow-primary-600/20' : 'bg-gray-50 text-gray-900 hover:bg-gray-100 border border-gray-200' }}">
                                {{ $plan['price_monthly'] === 0 ? 'Get Started Free' : 'Start with '.$plan['label'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 sm:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative rounded-3xl gradient-bg px-8 py-16 sm:px-16 sm:py-20 text-center overflow-hidden">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyek0zNiAyNHYySDI0di0yaDEyeiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>
                    <div class="relative">
                        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to transform your prospecting?</h2>
                        <p class="text-lg text-primary-100 mb-8 max-w-xl mx-auto">Join teams already using AI to research prospects and write personalised outreach at scale.</p>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-primary-50 transition shadow-xl text-base">
                            Get Started — It's Free
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-6 w-auto" />
                    <span class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <a href="{{ route('login') }}" class="hover:text-gray-700 transition">Log In</a>
                    <a href="{{ route('register') }}" class="hover:text-gray-700 transition">Sign Up</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
