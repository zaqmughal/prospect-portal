<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\OrganizationInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public string $token = '';

    public ?OrganizationInvitation $invitation = null;

    public bool $invalidToken = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->invitation = OrganizationInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->with('organization')
            ->first();

        if (! $this->invitation) {
            $this->invalidToken = true;

            return;
        }

        if (Auth::check()) {
            $this->accept();
        }
    }

    public function accept(): void
    {
        if ($this->invalidToken || ! $this->invitation) {
            return;
        }

        $user = Auth::user();

        if (! $user) {
            $this->redirect(
                route('register', ['invitation' => $this->token]),
                navigate: true
            );

            return;
        }

        $organization = $this->invitation->organization;

        if (! $organization->isMember($user)) {
            $organization->users()->attach($user->id, ['role' => $this->invitation->role]);
        }

        $this->invitation->update(['accepted_at' => now()]);

        $user->update(['current_organization_id' => $organization->id]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.organizations.accept-invitation');
    }
}
