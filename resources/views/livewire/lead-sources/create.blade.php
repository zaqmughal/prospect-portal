<div>
    <div class="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/50 dark:shadow-none sm:p-8">
        <div class="mb-5 flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400" aria-hidden="true">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
            </span>
            <div>
                <h2 class="text-base font-semibold tracking-tight text-gray-900 dark:text-gray-100">Generate with AI</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Get a suggested name and search queries from a short description.</p>
            </div>
        </div>
        <div class="space-y-5">
            <div class="mt-4">
                <label for="aiDescription" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Describe the prospects you want to find</label>
                <textarea
                    id="aiDescription"
                    wire:model="aiDescription"
                    rows="4"
                    class="block w-full rounded-lg border-gray-300 shadow-sm transition-colors placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder:text-gray-500"
                    placeholder="e.g. UK charities offering training in Sussex"
                    @if($generating) disabled @endif
                ></textarea>
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Be specific about sector, location, or type of organisation.</p>
                <x-input-error :messages="$errors->get('aiDescription')" class="mt-2" />
            </div>
            <div class="mt-4">
                <label for="icpId" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Use ICP for context <span class="font-normal text-gray-500 dark:text-gray-400">(optional)</span></label>
                <select
                    id="icpId"
                    wire:model="icpId"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    @if($generating) disabled @endif
                >
                    <option value="">None</option>
                    @foreach($icps as $icp)
                        <option value="{{ $icp->id }}">{{ $icp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-1 mt-4">
                <button
                    type="button"
                    wire:click="generateWithAi"
                    wire:loading.attr="disabled"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-800 sm:w-auto"
                >
                    <span wire:loading.remove wire:target="generateWithAi" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                        Generate with AI
                    </span>
                    <span wire:loading wire:target="generateWithAi" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Generating…
                    </span>
                </button>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Suggestions will appear in the form below. You can edit them before saving.</p>
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" wire:model="name" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label value="Queries (one per line)" />
            @foreach($queries as $index => $query)
                <div class="flex gap-2 mt-2" wire:key="query-{{ $index }}">
                    <x-text-input wire:model="queries.{{ $index }}" class="flex-1" placeholder="e.g. site:.org.uk training Brighton" />
                    @if(count($queries) > 1)
                        <button type="button" wire:click="removeQuery({{ $index }})" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                    @endif
                </div>
            @endforeach
            <button type="button" wire:click="addQuery" class="mt-2 text-sm text-blue-600 hover:text-blue-900">Add query</button>
            <x-input-error :messages="$errors->get('queries')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="perQueryLimit" value="Results per query" />
            <x-text-input id="perQueryLimit" type="number" wire:model="perQueryLimit" class="mt-1 block w-full" min="1" max="50" />
            <x-input-error :messages="$errors->get('perQueryLimit')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="cadence" value="Cadence" />
            <select id="cadence" wire:model="cadence" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($cadences as $c)
                    <option value="{{ $c->value }}">{{ $c->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-4">
            <x-primary-button type="submit">Create lead source</x-primary-button>
            <a href="{{ route('lead-sources.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
