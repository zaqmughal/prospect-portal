<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly OrganizationInvitation $invitation
    ) {}

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $orgName = $this->invitation->organization->name;
        $acceptUrl = route('invitations.accept', ['token' => $this->invitation->token]);

        return (new MailMessage)
            ->subject("You're invited to join {$orgName} on {$appName}")
            ->greeting('Hello!')
            ->line("You've been invited to join **{$orgName}** on {$appName}.")
            ->line("You'll be joining as a **{$this->invitation->role}**, with access to your team's prospects, lead sources and playbooks.")
            ->action('Accept Invitation', $acceptUrl)
            ->line('If you weren\'t expecting this invitation, you can safely ignore this email.')
            ->salutation("Thanks,\nThe {$appName} team");
    }
}
