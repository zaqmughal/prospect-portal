<div>
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
            <x-primary-button type="submit">Update lead source</x-primary-button>
            <a href="{{ route('lead-sources.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50" wire:navigate>Cancel</a>
        </div>
    </form>
</div>
