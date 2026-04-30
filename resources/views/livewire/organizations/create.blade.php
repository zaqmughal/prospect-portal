<div class="max-w-xl mx-auto mt-10">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Create Your Organisation') }}</h2>
        <p class="text-sm text-gray-600 mb-6">{{ __('To get started, create an organisation for your team.') }}</p>

        <form wire:submit="save" class="space-y-6">
            <div>
                <x-input-label for="name" :value="__('Organisation Name')" />
                <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required autofocus placeholder="Acme Ltd" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Create Organisation') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
