<?php

namespace Tests\Unit;

use App\Services\SmsService;
use PHPUnit\Framework\TestCase;

class SmsServiceTest extends TestCase
{
    public function test_phone_log_context_omits_full_phone_number(): void
    {
        $method = new \ReflectionMethod(SmsService::class, 'phoneLogContext');

        $context = $method->invoke(null, '+13605550123');

        $this->assertSame('0123', $context['recipient_last4']);
        $this->assertSame(hash('sha256', '+13605550123'), $context['recipient_hash']);
        $this->assertNotContains('+13605550123', $context);
        $this->assertNotContains('3605550123', $context);
    }
}
