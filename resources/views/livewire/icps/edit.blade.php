<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <form wire:submit="save" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="sectors" :value="__('Target Sectors (comma-separated)')" />
            <x-text-input wire:model="sectors" id="sectors" type="text" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('sectors')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input wire:model="is_default" id="is_default" type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
            <label for="is_default" class="ml-2 block text-sm text-gray-900">Set as default ICP</label>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            <a href="{{ route('icps.index') }}" class="text-gray-600 hover:text-gray-900" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
