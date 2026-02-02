<?php

namespace App\Notifications;

use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AdminInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = route('admin.invitations.accept', ['token' => $this->invitation->token]);
        $roleName = $this->invitation->role === 'super_admin' ? 'Super Admin' : 'Admin';
        $expiresAt = $this->invitation->expires_at->format('F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject('Admin Invitation - Traxtar')
            ->greeting('Hello!')
            ->line("You have been invited to join Traxtar as a **{$roleName}**.")
            ->line('Click the button below to accept the invitation and set up your account. This link will expire in 24 hours.')
            ->action('Accept Invitation', $acceptUrl)
            ->line("This invitation expires on: **{$expiresAt}**")
            ->line('If you did not expect to receive this invitation, you can safely ignore this email.')
            ->salutation('Best regards, The Traxtar Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'email' => $this->invitation->email,
            'role' => $this->invitation->role,
        ];
    }
}
