<?php

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest-wide')] class extends Component
{
    public string $name = '';
    public string $company_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $invitation_token = null;
    public string $selected_plan = 'free';
    public int $step = 1;

    public function mount(): void
    {
        $this->invitation_token = request()->query('invitation');
        $this->selected_plan = request()->query('plan', 'free');

        if (! array_key_exists($this->selected_plan, config('plans', []))) {
            $this->selected_plan = 'free';
        }

        if ($this->invitation_token) {
            $invitation = OrganizationInvitation::where('token', $this->invitation_token)
                ->whereNull('accepted_at')
                ->first();

            if ($invitation) {
                $this->email = $invitation->email;
                $this->step = 2;
            }
        }
    }

    public function selectPlan(string $plan): void
    {
        if (array_key_exists($plan, config('plans', []))) {
            $this->selected_plan = $plan;
        }
    }

    public function continueToDetails(): void
    {
        $this->step = 2;
    }

    public function backToPlan(): void
    {
        if (! $this->invitation_token) {
            $this->step = 1;
        }
    }

    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];

        if (! $this->invitation_token) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
        }

        $validated = $this->validate($rules);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if ($this->invitation_token) {
            $invitation = OrganizationInvitation::where('token', $this->invitation_token)
                ->whereNull('accepted_at')
                ->first();

            if ($invitation) {
                $invitation->organization->users()->attach($user->id, ['role' => $invitation->role]);
                $invitation->update(['accepted_at' => now()]);
                $user->update(['current_organization_id' => $invitation->organization_id]);
            }

            event(new Registered($user));
            Auth::login($user);

            $this->redirect(route('dashboard', absolute: false), navigate: true);
            return;
        }

        $slug = Str::slug($validated['company_name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $isPaidPlan = $this->selected_plan !== 'free';
        $planConfig = config("plans.{$this->selected_plan}");
        $stripeConfigured = $isPaidPlan && $planConfig && ! empty($planConfig['stripe_price_id']);

        $organization = Organization::create([
            'name' => $validated['company_name'],
            'slug' => $slug,
            'owner_id' => $user->id,
            'plan' => $stripeConfigured ? 'free' : $this->selected_plan,
        ]);

        $organization->users()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_organization_id' => $organization->id]);

        event(new Registered($user));
        Auth::login($user);

        if ($stripeConfigured) {
            try {
                $checkout = $organization
                    ->newSubscription('default', $planConfig['stripe_price_id'])
                    ->checkout([
                        'success_url' => route('dashboard').'?checkout=success',
                        'cancel_url' => route('dashboard').'?checkout=cancelled',
                    ]);

                $this->redirect($checkout->url);

                return;
            } catch (\Throwable $e) {
                Log::error('Stripe checkout failed during registration', [
                    'organization_id' => $organization->id,
                    'plan' => $this->selected_plan,
                    'error' => $e->getMessage(),
                ]);

                $organization->update(['plan' => $this->selected_plan]);

                session()->flash('billing_error', 'Your account was created but we could not connect to our payment provider. Please set up your subscription from the billing page.');

                $this->redirect(route('settings.billing'));

                return;
            }
        }

        if ($isPaidPlan && ! $stripeConfigured) {
            $organization->update(['plan' => $this->selected_plan]);

            session()->flash('billing_notice', 'Your account was created on the '.($planConfig['label'] ?? $this->selected_plan).' plan. You can manage your subscription from the billing page.');

            $this->redirect(route('settings.billing'));

            return;
        }

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    {{-- Step Indicator --}}
    @unless($invitation_token)
    <div class="mb-8">
        <div class="flex items-center justify-center gap-3">
            <button wire:click="backToPlan" type="button"
                class="flex items-center gap-2 {{ $step === 1 ? 'text-primary-600' : 'text-gray-400 hover:text-gray-600' }} transition">
                <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold {{ $step === 1 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500' }}">1</span>
                <span class="text-sm font-medium hidden sm:inline">Choose Plan</span>
            </button>

            <div class="w-12 h-px bg-gray-300"></div>

            <div class="flex items-center gap-2 {{ $step === 2 ? 'text-primary-600' : 'text-gray-400' }}">
                <span class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold {{ $step === 2 ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-500' }}">2</span>
                <span class="text-sm font-medium hidden sm:inline">Create Account</span>
            </div>
        </div>
    </div>
    @endunless

    {{-- Step 1: Plan Selection --}}
    @if ($step === 1 && ! $invitation_token)
        <div>
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Choose your plan</h2>
            <p class="text-gray-500 text-center text-sm mb-8">Start free and upgrade anytime. No credit card required for the free plan.</p>

            @php $plans = config('plans'); @endphp

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($plans as $planKey => $plan)
                    <button wire:click="selectPlan('{{ $planKey }}')" type="button"
                        class="relative text-left p-5 rounded-xl border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                            {{ $selected_plan === $planKey
                                ? 'border-primary-500 bg-primary-50 shadow-md'
                                : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm' }}">

                        @if ($planKey === 'starter')
                            <span class="absolute -top-2.5 left-4 inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-600 text-white text-[10px] font-bold uppercase tracking-wider">Popular</span>
                        @endif

                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $plan['label'] }}</h3>
                                <div class="mt-1">
                                    @if ($plan['price_monthly'] === 0)
                                        <span class="text-2xl font-bold text-gray-900">Free</span>
                                    @else
                                        <span class="text-2xl font-bold text-gray-900">&pound;{{ $plan['price_monthly'] }}</span>
                                        <span class="text-gray-500 text-xs">/mo</span>
                                    @endif
                                </div>
                            </div>

                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-1
                                {{ $selected_plan === $planKey ? 'border-primary-600 bg-primary-600' : 'border-gray-300' }}">
                                @if ($selected_plan === $planKey)
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                @endif
                            </div>
                        </div>

                        <ul class="space-y-1.5 text-xs text-gray-600" role="list">
                            <li class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ $plan['accounts_limit'] ?? 'Unlimited' }} accounts
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ $plan['research_runs_per_month'] ?? 'Unlimited' }} runs/mo
                            </li>
                            <li class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                {{ $plan['team_members_limit'] }} team {{ $plan['team_members_limit'] === 1 ? 'member' : 'members' }}
                            </li>
                            <li class="flex items-center gap-1.5">
                                @if ($plan['ai_generated_icps'])
                                    <svg class="h-3.5 w-3.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                @else
                                    <svg class="h-3.5 w-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                @endif
                                AI generation
                            </li>
                        </ul>
                    </button>
                @endforeach
            </div>

            <div class="mt-8">
                <button wire:click="continueToDetails" type="button"
                    class="w-full flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition shadow-sm">
                    Continue with {{ config("plans.{$selected_plan}.label") }}
                    <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Step 2: Account Details --}}
    @if ($step === 2)
        <div>
            @unless($invitation_token)
                <div class="mb-6 p-3 rounded-lg bg-primary-50 border border-primary-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-sm font-medium text-primary-900">
                            {{ config("plans.{$selected_plan}.label") }} plan
                            @if (config("plans.{$selected_plan}.price_monthly") > 0)
                                — &pound;{{ config("plans.{$selected_plan}.price_monthly") }}/mo
                            @else
                                — Free
                            @endif
                        </span>
                    </div>
                    <button wire:click="backToPlan" type="button" class="text-xs text-primary-600 hover:text-primary-800 font-medium">
                        Change
                    </button>
                </div>
            @endunless

            <form wire:submit="register">
                <div>
                    <x-input-label for="name" :value="__('Full Name')" />
                    <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                @unless($invitation_token)
                <div class="mt-4">
                    <x-input-label for="company_name" :value="__('Company Name')" />
                    <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" name="company_name" required autocomplete="organization" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>
                @endunless

                <div class="mt-4">
                    <x-input-label for="email" :value="__('Work Email')" />
                    <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" :disabled="(bool) $invitation_token" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <x-primary-button class="w-full justify-center py-3">
                        @if ($selected_plan !== 'free' && ! $invitation_token)
                            {{ __('Create Account & Continue to Payment') }}
                        @else
                            {{ __('Create Account') }}
                        @endif
                    </x-primary-button>
                </div>

                @if ($selected_plan !== 'free' && ! $invitation_token)
                    <p class="mt-3 text-xs text-gray-500 text-center">You'll be redirected to our secure payment partner Stripe to complete your subscription.</p>
                @endif

                <div class="mt-4 text-center">
                    <a class="text-sm text-gray-600 hover:text-gray-900 transition" href="{{ route('login') }}" wire:navigate>
                        {{ __('Already have an account? Log in') }}
                    </a>
                </div>
            </form>
        </div>
    @endif
</div>
