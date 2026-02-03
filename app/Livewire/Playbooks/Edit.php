<?php

declare(strict_types=1);

namespace App\Livewire\Playbooks;

use App\Models\Playbook;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Edit extends Component
{
    #[Locked]
    public int $playbookId;

    public string $name = '';

    public string $angle = '';

    public string $dm_template = '';

    public string $email_template = '';

    public bool $is_active = true;

    public function mount(int $playbookId): void
    {
        $this->playbookId = $playbookId;
        $playbook = Playbook::findOrFail($playbookId);

        $this->name = $playbook->name;
        $this->angle = $playbook->angle;
        $this->dm_template = $playbook->dm_template;
        $this->email_template = $playbook->email_template;
        $this->is_active = $playbook->is_active;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'angle' => ['required', 'string', 'max:255'],
            'dm_template' => ['required', 'string'],
            'email_template' => ['required', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Playbook::findOrFail($this->playbookId)->update([
            'name' => $this->name,
            'angle' => $this->angle,
            'dm_template' => $this->dm_template,
            'email_template' => $this->email_template,
            'is_active' => $this->is_active,
        ]);

        $this->redirect(route('playbooks.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.playbooks.edit');
    }
}
