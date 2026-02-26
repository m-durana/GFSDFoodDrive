<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class Adopter
{
    use Notifiable;

    public function __construct(
        public string $email = '',
        public string $phone = '',
        public string $name = '',
    ) {}

    public function routeNotificationForMail(): ?string
    {
        return $this->email ?: null;
    }

    public function routeNotificationForVonage(): ?string
    {
        return $this->phone ?: null;
    }
}
