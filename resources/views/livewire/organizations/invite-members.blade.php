<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm text-green-700">{{ session('message') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Current Members -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Team Members') }}</h3>

        <div class="divide-y divide-gray-200">
            @foreach ($members as $member)
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->pivot->role === 'owner' ? 'bg-purple-100 text-purple-800' : ($member->pivot->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($member->pivot->role) }}
                        </span>

                        @if ($isAdmin && $member->pivot->role !== 'owner' && $member->id !== auth()->id())
                            <button wire:click="removeMember({{ $member->id }})"
                                    wire:confirm="Are you sure you want to remove this member?"
                                    class="text-sm text-red-600 hover:text-red-800">
                                Remove
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Pending Invitations -->
    @if ($pendingInvitations->isNotEmpty())
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Pending Invitations') }}</h3>

            <div class="divide-y divide-gray-200">
                @foreach ($pendingInvitations as $invitation)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-900">{{ $invitation->email }}</p>
                            <p class="text-xs text-gray-500">Invited {{ $invitation->created_at->diffForHumans() }} &middot; {{ ucfirst($invitation->role) }}</p>
                        </div>
                        @if ($isAdmin)
                            <button wire:click="cancelInvitation({{ $invitation->id }})"
                                    class="text-sm text-red-600 hover:text-red-800">
                                Cancel
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Invite Form -->
    @if ($isAdmin)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Invite a Team Member') }}</h3>

            <form wire:submit="invite" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label for="email" :value="__('Email Address')" />
                        <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" required placeholder="colleague@example.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select wire:model="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <x-primary-button>{{ __('Send Invitation') }}</x-primary-button>
                </div>
            </form>
        </div>
    @endif
</div>
