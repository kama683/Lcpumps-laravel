<?php

namespace Tests\Unit;

use App\Services\RateLimiterService;
use Tests\TestCase;

class RateLimiterServiceTest extends TestCase
{
    public function test_allows_up_to_the_limit_then_blocks(): void
    {
        $limiter = new RateLimiterService;

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($limiter->attempt('test-key', 5, 600), "attempt {$i} should be allowed");
        }

        $this->assertFalse($limiter->attempt('test-key', 5, 600));
    }

    public function test_keys_are_independent(): void
    {
        $limiter = new RateLimiterService;

        for ($i = 0; $i < 5; $i++) {
            $limiter->attempt('key-a', 5, 600);
        }

        $this->assertFalse($limiter->attempt('key-a', 5, 600));
        $this->assertTrue($limiter->attempt('key-b', 5, 600));
    }
}
