<div>
    <div class="mb-4 flex flex-wrap gap-4 items-center">
        <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
        @if(config('discovery.approve_before_import'))
            <button wire:click="approveSelected" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                Approve selected
            </button>
            <button wire:click="promoteApproved" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Promote approved to accounts
            </button>
        @endif
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if(config('discovery.approve_before_import'))
                            <th scope="col" class="px-6 py-3 text-left w-12"></th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        @if(config('discovery.approve_before_import'))
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($candidates as $candidate)
                        <tr wire:key="candidate-{{ $candidate->id }}">
                            @if(config('discovery.approve_before_import'))
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($candidate->status->value === 'new')
                                        <input type="checkbox" wire:model.live="selected" value="{{ $candidate->id }}" class="rounded border-gray-300">
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $candidate->domain }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $candidate->url }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $candidate->title ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($candidate->status->color())
                                        @case('green') bg-green-100 text-green-800 @break
                                        @case('red') bg-red-100 text-red-800 @break
                                        @case('orange') bg-orange-100 text-orange-800 @break
                                        @case('yellow') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ $candidate->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $candidate->reason ?? '—' }}</td>
                            @if(config('discovery.approve_before_import'))
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    @if($candidate->status->value === 'new')
                                        <button wire:click="approve({{ $candidate->id }})" class="text-green-600 hover:text-green-900">Approve</button>
                                        <button wire:click="reject({{ $candidate->id }})" class="text-red-600 hover:text-red-900">Reject</button>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ config('discovery.approve_before_import') ? 7 : 5 }}" class="px-6 py-4 text-center text-gray-500">No candidates for this run.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
