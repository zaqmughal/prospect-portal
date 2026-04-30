<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
    public string $name = '';

    public string $slug = '';

    public function mount(): void
    {
        $organization = Auth::user()->currentOrganization;

        if ($organization) {
            $this->name = $organization->name;
            $this->slug = $organization->slug;
        }
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        $orgId = Auth::user()->current_organization_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', "unique:organizations,slug,{$orgId}"],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isAdmin($user)) {
            abort(403, 'Only admins can update organisation settings.');
        }

        $organization->update([
            'name' => $this->name,
            'slug' => Str::slug($this->slug),
        ]);

        session()->flash('message', 'Organisation settings updated.');
    }

    public function render(): View
    {
        $organization = Auth::user()->currentOrganization;

        return view('livewire.organizations.settings', [
            'isAdmin' => $organization?->isAdmin(Auth::user()) ?? false,
            'currentPlan' => $organization?->plan ?? 'free',
            'memberCount' => $organization?->users()->count() ?? 0,
        ]);
    }
}
