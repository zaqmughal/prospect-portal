<?php

declare(strict_types=1);

namespace App\Livewire\Icps;

use App\Models\Icp;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public string $sectors = '';

    public bool $is_default = false;

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sectors' => ['nullable', 'string'],
            'is_default' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->is_default) {
            Icp::where('is_default', true)->update(['is_default' => false]);
        }

        $sectors = $this->sectors ? array_map('trim', explode(',', $this->sectors)) : null;

        Icp::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'sectors' => $sectors,
            'is_default' => $this->is_default,
        ]);

        $this->redirect(route('icps.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.icps.create');
    }
}
