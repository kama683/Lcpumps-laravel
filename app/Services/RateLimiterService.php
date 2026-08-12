<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Fixed-window rate limiter shared by the public contact form (Stage 6) and
 * the admin login form (Stage 7) — ported from the source's checkRateLimit
 * (lib/rate-limit.ts), which kept buckets in a process-local Map.
 *
 * That approach assumed a single Node process behind Caddy. Plesk serves
 * this app through PHP-FPM with several worker processes, each with its own
 * memory, so an in-memory bucket would give an effective limit of
 * `limit * worker count` instead of `limit`. This wraps Illuminate's
 * RateLimiter facade instead, backed by CACHE_STORE (the "database" cache
 * driver in this app — see config/cache.php), which all workers share.
 */
class RateLimiterService
{
    /**
     * Records one attempt for $key and reports whether it was allowed.
     * Returns false once $maxAttempts attempts have been recorded for $key
     * within the trailing $decaySeconds window, without recording this one.
     */
    public function attempt(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        RateLimiter::hit($key, $decaySeconds);

        return true;
    }

    /** Resets the counter for $key — e.g. after a successful login, so a legitimate user's earlier failed attempts don't linger. */
    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }
}
