<?php

use App\Livewire\Actions\Logout;
use App\Models\Organization;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function switchOrganization(int $organizationId): void
    {
        $organization = Organization::findOrFail($organizationId);
        auth()->user()->switchOrganization($organization);

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100" role="banner">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')" wire:navigate>
                        {{ __('Accounts') }}
                    </x-nav-link>
                    <x-nav-link :href="route('lead-sources.index')" :active="request()->routeIs('lead-sources.*') || request()->routeIs('lead-source-runs.*')" wire:navigate>
                        {{ __('Lead Sources') }}
                    </x-nav-link>
                    <x-nav-link :href="route('icps.index')" :active="request()->routeIs('icps.*')" wire:navigate>
                        {{ __('ICPs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('playbooks.index')" :active="request()->routeIs('playbooks.*')" wire:navigate>
                        {{ __('Playbooks') }}
                    </x-nav-link>
                    <x-nav-link :href="route('settings')" :active="request()->routeIs('settings', 'settings.*')" wire:navigate>
                        {{ __('Settings') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Organization Switcher & Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                @if(auth()->user()->organizations->count() > 1)
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-600 bg-gray-50 hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150">
                                <svg class="w-4 h-4 me-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                {{ auth()->user()->currentOrganization?->name ?? 'Select Org' }}
                                <svg class="fill-current h-4 w-4 ms-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @foreach(auth()->user()->organizations as $org)
                                <button wire:click="switchOrganization({{ $org->id }})" class="w-full text-start">
                                    <x-dropdown-link class="{{ $org->id === auth()->user()->current_organization_id ? 'font-semibold bg-gray-50' : '' }}">
                                        {{ $org->name }}
                                        @if($org->id === auth()->user()->current_organization_id)
                                            <span class="text-primary-500 ms-1">&check;</span>
                                        @endif
                                    </x-dropdown-link>
                                </button>
                            @endforeach
                        </x-slot>
                    </x-dropdown>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings')" wire:navigate>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings.team')" wire:navigate>
                            {{ __('Team') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings.billing')" wire:navigate>
                            {{ __('Billing') }}
                        </x-dropdown-link>

                        <div class="border-t border-gray-200"></div>

                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')" wire:navigate>
                {{ __('Accounts') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('lead-sources.index')" :active="request()->routeIs('lead-sources.*') || request()->routeIs('lead-source-runs.*')" wire:navigate>
                {{ __('Lead Sources') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('icps.index')" :active="request()->routeIs('icps.*')" wire:navigate>
                {{ __('ICPs') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('playbooks.index')" :active="request()->routeIs('playbooks.*')" wire:navigate>
                {{ __('Playbooks') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings')" :active="request()->routeIs('settings', 'settings.*')" wire:navigate>
                {{ __('Settings') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                @if(auth()->user()->currentOrganization)
                    <div class="font-medium text-xs text-gray-400 mt-0.5">{{ auth()->user()->currentOrganization->name }}</div>
                @endif
            </div>

            @if(auth()->user()->organizations->count() > 1)
                <div class="mt-3 px-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Switch Organisation</p>
                </div>
                @foreach(auth()->user()->organizations as $org)
                    <button wire:click="switchOrganization({{ $org->id }})" class="w-full text-start">
                        <x-responsive-nav-link :active="$org->id === auth()->user()->current_organization_id">
                            {{ $org->name }}
                        </x-responsive-nav-link>
                    </button>
                @endforeach
            @endif

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings.team')" wire:navigate>
                    {{ __('Team') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings.billing')" wire:navigate>
                    {{ __('Billing') }}
                </x-responsive-nav-link>

                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
