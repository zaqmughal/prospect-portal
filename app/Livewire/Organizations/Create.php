<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->name);
        $originalSlug = $slug;
        $counter = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $user = Auth::user();

        $organization = Organization::create([
            'name' => $this->name,
            'slug' => $slug,
            'owner_id' => $user->id,
            'plan' => 'free',
        ]);

        $organization->users()->attach($user->id, ['role' => 'owner']);
        $user->update(['current_organization_id' => $organization->id]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.organizations.create');
    }
}
