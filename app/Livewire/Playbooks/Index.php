<?php

declare(strict_types=1);

namespace App\Livewire\Playbooks;

use App\Models\Playbook;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function delete(int $playbookId): void
    {
        Playbook::findOrFail($playbookId)->delete();
        $this->dispatch('notify', message: 'Playbook deleted');
    }

    public function toggleActive(int $playbookId): void
    {
        $playbook = Playbook::findOrFail($playbookId);
        $playbook->update(['is_active' => ! $playbook->is_active]);
        $this->dispatch('notify', message: 'Playbook '.($playbook->is_active ? 'activated' : 'deactivated'));
    }

    public function render(): View
    {
        return view('livewire.playbooks.index', [
            'playbooks' => Playbook::orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }
}
