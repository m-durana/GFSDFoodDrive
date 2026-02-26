<?php

namespace App\Notifications;

use App\Models\Child;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdoptionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Child $child,
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof Adopter && $notifiable->email) {
            return ['mail'];
        }

        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $child = $this->child;
        $gender = $child->gender ?? 'Child';
        $age = $child->age ?? '?';
        $familyNumber = $child->family?->family_number ?? '?';
        $deadline = $child->adoption_deadline?->format('F j, Y') ?? 'soon';
        $confirmationUrl = route('adopt.confirmation', $child->adoption_token);

        return (new MailMessage)
            ->subject("Reminder: Gift Drop-off Due {$deadline}")
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a friendly reminder that the gift for **{$gender}, Age {$age}** (Family #{$familyNumber}) is due by **{$deadline}**.")
            ->line("Please label the gift with **Family #{$familyNumber}** when dropping it off.")
            ->action('View Your Tag', $confirmationUrl)
            ->line('Thank you for your generosity!');
    }
}
