<?php

declare(strict_types=1);

namespace App\Livewire\Icps;

use App\Models\Icp;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function delete(int $icpId): void
    {
        Icp::findOrFail($icpId)->delete();
        $this->dispatch('notify', message: 'ICP deleted');
    }

    public function setDefault(int $icpId): void
    {
        Icp::where('is_default', true)->update(['is_default' => false]);
        Icp::findOrFail($icpId)->update(['is_default' => true]);
        $this->dispatch('notify', message: 'Default ICP updated');
    }

    public function render(): View
    {
        return view('livewire.icps.index', [
            'icps' => Icp::orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }
}
