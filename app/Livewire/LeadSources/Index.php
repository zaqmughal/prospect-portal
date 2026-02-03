<?php

declare(strict_types=1);

namespace App\Livewire\LeadSources;

use App\Jobs\RunLeadSourceDiscovery;
use App\Models\LeadSource;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function runNow(int $leadSourceId): void
    {
        $source = LeadSource::where('user_id', Auth::id())->findOrFail($leadSourceId);
        RunLeadSourceDiscovery::dispatch($source, 'manual');
        $this->dispatch('notify', message: 'Discovery run queued for '.$source->name);
    }

    public function delete(int $leadSourceId): void
    {
        LeadSource::where('user_id', Auth::id())->findOrFail($leadSourceId)->delete();
        $this->dispatch('notify', message: 'Lead source deleted');
    }

    public function render(): View
    {
        return view('livewire.lead-sources.index', [
            'leadSources' => LeadSource::where('user_id', Auth::id())
                ->with('latestRun')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
