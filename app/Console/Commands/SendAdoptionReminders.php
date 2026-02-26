<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\Setting;
use App\Notifications\Adopter;
use App\Notifications\AdoptionReminder;
use Illuminate\Console\Command;

class SendAdoptionReminders extends Command
{
    protected $signature = 'adopt:send-reminders';

    protected $description = 'Send reminder notifications to adopters whose deadline is in 3 days';

    public function handle(): int
    {
        if (Setting::get('notifications_enabled', '0') !== '1') {
            $this->info('Notifications are disabled. Enable in Admin Settings to send reminders.');
            return self::SUCCESS;
        }

        $children = Child::with('family')
            ->whereNotNull('adoption_token')
            ->where('gift_dropped_off', false)
            ->where('adoption_reminder_sent', false)
            ->whereNotNull('adoption_deadline')
            ->whereDate('adoption_deadline', '=', now()->addDays(3)->toDateString())
            ->get();

        if ($children->isEmpty()) {
            $this->info('No reminders to send today.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($children as $child) {
            $email = $child->adopter_email ?? '';
            $phone = $child->adopter_phone ?? '';
            $name = $child->adopter_name ?? 'Adopter';

            if (!$email && !$phone) {
                continue;
            }

            $adopter = new Adopter($email, $phone, $name);
            $adopter->notify(new AdoptionReminder($child));

            $child->update(['adoption_reminder_sent' => true]);
            $sent++;

            $this->line("  Sent reminder for child #{$child->id} to {$name}");
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
