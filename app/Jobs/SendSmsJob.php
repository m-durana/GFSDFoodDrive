<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends an SMS via Twilio off the request thread.
 *
 * The synchronous Twilio HTTP call adds 500-2000ms to whatever request
 * triggered the notification (typically a Family/Child observer write).
 * Dispatching here means the user-facing request returns immediately and
 * the SMS is delivered by the queue worker.
 */
class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public string $to,
        public string $message,
    ) {
    }

    public function handle(): void
    {
        SmsService::send($this->to, $this->message);
    }
}
