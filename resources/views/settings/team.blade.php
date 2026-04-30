<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('settings.partials.tabs', ['activeTab' => 'team'])
            <x-page-intro id="settings.team" title="Invite teammates and manage roles">
                <p>
                    Invitees receive an email with a one-time link to join your organisation — they'll see the same accounts, lead sources, ICPs, and playbooks you do. Roles control permissions: <strong>members</strong> can use the platform, <strong>admins</strong> can also manage settings and team, and the <strong>owner</strong> additionally controls billing.
                </p>
                <p class="mt-2 text-xs">
                    Your plan limits how many people can join — see <a href="{{ route('settings.billing') }}" class="underline hover:no-underline" wire:navigate>Billing</a> if you need more seats.
                </p>
            </x-page-intro>

            <livewire:organizations.invite-members />
        </div>
    </div>
</x-app-layout>
