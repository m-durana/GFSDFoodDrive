<?php

namespace App\Notifications;

use App\Models\Child;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdoptionConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Child $child,
        protected string $token,
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
        $deadline = $child->adoption_deadline?->format('F j, Y') ?? 'TBD';
        $confirmationUrl = route('adopt.confirmation', $this->token);

        return (new MailMessage)
            ->subject("Adopt-a-Tag Confirmation - {$gender}, Age {$age}")
            ->greeting("Thank you, {$notifiable->name}!")
            ->line("You've adopted a gift tag for a **{$gender}, Age {$age}** (Family #{$familyNumber}).")
            ->line("Please drop off the gift by **{$deadline}**.")
            ->line("Label the gift with **Family #{$familyNumber}**.")
            ->action('View Your Tag', $confirmationUrl)
            ->line('Thank you for making the holidays brighter for a child in Granite Falls!');
    }
}
