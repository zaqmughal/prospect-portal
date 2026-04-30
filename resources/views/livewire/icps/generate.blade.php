<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    @if (!$canGenerate)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">AI-Generated ICPs</h3>
            <p class="text-sm text-gray-600 mb-4">This feature requires a Starter plan or above.</p>
            <a href="{{ route('settings.billing') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700" wire:navigate>
                Upgrade Your Plan
            </a>
        </div>
    @else
        <h3 class="text-lg font-medium text-gray-900 mb-4">Generate ICP with AI</h3>
        <p class="text-sm text-gray-600 mb-6">Describe your business and target market, and AI will generate a complete Ideal Customer Profile for you.</p>

        <form wire:submit="generate" class="space-y-6">
            <div>
                <x-input-label for="businessDescription" :value="__('Describe Your Business')" />
                <textarea wire:model="businessDescription" id="businessDescription" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="We're a web agency specialising in accessible website design for charities and public sector organisations in the UK..."
                    required></textarea>
                <x-input-error :messages="$errors->get('businessDescription')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="targetMarket" :value="__('Target Market (optional)')" />
                <textarea wire:model="targetMarket" id="targetMarket" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="SMEs in education and healthcare sectors, 10-200 employees, based in South East England..."></textarea>
                <x-input-error :messages="$errors->get('targetMarket')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button wire:loading.attr="disabled" wire:target="generate">
                    <span wire:loading.remove wire:target="generate">Generate ICP</span>
                    <span wire:loading wire:target="generate">Generating...</span>
                </x-primary-button>
                <a href="{{ route('icps.index') }}" class="text-gray-600 hover:text-gray-900" wire:navigate>Cancel</a>
            </div>
        </form>
    @endif
</div>
