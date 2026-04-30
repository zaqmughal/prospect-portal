<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm text-green-700">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Organisation Info -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Organisation Details') }}</h3>

        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Plan</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ ucfirst($currentPlan) }}</dd>
            </div>
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Team Members</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $memberCount }}</dd>
            </div>
            <div class="border rounded-lg p-4">
                <dt class="text-sm font-medium text-gray-500">Your Role</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900">{{ ucfirst(auth()->user()->organizationRole() ?? 'Member') }}</dd>
            </div>
        </dl>

        @if ($isAdmin)
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-input-label for="name" :value="__('Organisation Name')" />
                    <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="slug" :value="__('URL Slug')" />
                    <x-text-input wire:model="slug" id="slug" type="text" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Used in URLs. Only letters, numbers, dashes, and underscores.</p>
                </div>

                <div>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        @else
            <p class="text-sm text-gray-600">Contact an admin to change organisation settings.</p>
        @endif
    </div>
</div>
