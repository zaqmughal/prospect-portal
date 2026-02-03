<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Company Name')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="url" :value="__('Website URL')" />
            <x-text-input wire:model="url" id="url" type="url" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('url')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="sector" :value="__('Sector')" />
                <x-text-input wire:model="sector" id="sector" type="text" class="mt-1 block w-full" />
                <x-input-error :messages="$errors->get('sector')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="size_band" :value="__('Company Size')" />
                <select wire:model="size_band" id="size_band" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select size...</option>
                    <option value="1-10">1-10 employees</option>
                    <option value="11-50">11-50 employees</option>
                    <option value="51-200">51-200 employees</option>
                    <option value="201-500">201-500 employees</option>
                    <option value="500+">500+ employees</option>
                </select>
                <x-input-error :messages="$errors->get('size_band')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="location" :value="__('Location')" />
            <x-text-input wire:model="location" id="location" type="text" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="notes" :value="__('Notes')" />
            <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            <a href="{{ route('accounts.show', $accountId) }}" class="text-gray-600 hover:text-gray-900" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
