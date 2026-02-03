<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="angle" :value="__('Angle')" />
            <x-text-input wire:model="angle" id="angle" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('angle')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="dm_template" :value="__('LinkedIn DM Template')" />
            <textarea wire:model="dm_template" id="dm_template" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm" required></textarea>
            <p class="mt-1 text-sm text-gray-500">Variables: {first_name}, {company_name}, {sector}, {main_signal}</p>
            <x-input-error :messages="$errors->get('dm_template')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email_template" :value="__('Email Template')" />
            <textarea wire:model="email_template" id="email_template" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm" required></textarea>
            <p class="mt-1 text-sm text-gray-500">Variables: {first_name}, {company_name}, {sector}, {key_signals}</p>
            <x-input-error :messages="$errors->get('email_template')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            <a href="{{ route('playbooks.index') }}" class="text-gray-600 hover:text-gray-900" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
