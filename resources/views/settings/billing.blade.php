<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing & Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('settings.partials.tabs', ['activeTab' => 'billing'])
            <x-page-intro id="settings.billing" title="Manage your subscription">
                <p>
                    Plans control your monthly research run limit, daily AI spend cap, team-member seats, and access to AI generators for ICPs and playbooks. Upgrades take effect immediately — checkout, plan changes, and invoice management all happen through the secure Stripe Customer Portal.
                </p>
                <p class="mt-2 text-xs">
                    Only the organisation owner can change plans or open the billing portal.
                </p>
            </x-page-intro>

            <livewire:billing.manage-subscription />
        </div>
    </div>
</x-app-layout>
