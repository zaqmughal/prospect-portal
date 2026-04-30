<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\OrganizationInvitation;
use App\Notifications\OrganizationInvitationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class InviteMembers extends Component
{
    public string $email = '';

    public string $role = 'member';

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member'],
        ];
    }

    public function invite(): void
    {
        $this->validate();

        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isAdmin($user)) {
            abort(403, 'You do not have permission to invite members.');
        }

        if ($organization->users()->where('email', $this->email)->exists()) {
            $this->addError('email', 'This user is already a member of the organisation.');

            return;
        }

        $existingInvitation = $organization->invitations()
            ->where('email', $this->email)
            ->whereNull('accepted_at')
            ->first();

        if ($existingInvitation) {
            $this->addError('email', 'An invitation has already been sent to this email address.');

            return;
        }

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => $this->email,
            'role' => $this->role,
            'token' => Str::random(64),
        ]);

        Notification::route('mail', $this->email)
            ->notify(new OrganizationInvitationNotification($invitation));

        $this->reset('email', 'role');
        $this->role = 'member';

        session()->flash('message', 'Invitation sent successfully.');
    }

    public function cancelInvitation(int $invitationId): void
    {
        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isAdmin($user)) {
            abort(403);
        }

        $invitation = $organization->invitations()
            ->where('id', $invitationId)
            ->whereNull('accepted_at')
            ->first();

        $invitation?->delete();
    }

    public function removeMember(int $userId): void
    {
        $user = Auth::user();
        $organization = $user->currentOrganization;

        if (! $organization || ! $organization->isAdmin($user)) {
            abort(403);
        }

        if ($organization->owner_id === $userId) {
            session()->flash('error', 'Cannot remove the organisation owner.');

            return;
        }

        if ($userId === $user->id) {
            session()->flash('error', 'You cannot remove yourself.');

            return;
        }

        $organization->users()->detach($userId);

        $memberUser = \App\Models\User::find($userId);
        if ($memberUser && $memberUser->current_organization_id === $organization->id) {
            $memberUser->update(['current_organization_id' => null]);
        }
    }

    public function render(): View
    {
        $organization = Auth::user()->currentOrganization;

        return view('livewire.organizations.invite-members', [
            'members' => $organization?->users()->orderByPivot('role')->get() ?? collect(),
            'pendingInvitations' => $organization?->invitations()->whereNull('accepted_at')->latest()->get() ?? collect(),
            'isAdmin' => $organization?->isAdmin(Auth::user()) ?? false,
        ]);
    }
}
