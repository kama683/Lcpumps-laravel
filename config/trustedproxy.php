<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies in front of PHP-FPM (Plesk/nginx)
    |--------------------------------------------------------------------------
    |
    | Read by Illuminate\Http\Middleware\TrustProxies, which is already part
    | of Laravel's default global middleware stack (no extra registration
    | needed) — it falls back to this file whenever
    | TrustProxies::at()/withHeaders() haven't been called from
    | bootstrap/app.php, which they aren't here.
    |
    | Comma-separated IP(s)/CIDR ranges, or "*" to trust whatever the direct
    | TCP peer is — safe *only* because PHP-FPM on Plesk is never reachable
    | except through that one local nginx hop (typically 127.0.0.1). See
    | DEPLOY.md's "TrustProxies" section for how to confirm the right value
    | once hosting is provisioned.
    |
    | Left empty until then: Symfony/Laravel's default behavior is to ignore
    | X-Forwarded-For entirely and report the raw TCP peer from
    | $request->ip() — safe, not a bug, and exactly why
    | ContactSubmissionHandler (Stage 6) and Admin\AuthController (Stage 7)
    | both call $request->ip() for their rate-limit keys instead of parsing
    | X-Forwarded-For by hand: this one setting controls IP-spoofing
    | resistance for both, instead of duplicating trust logic per call site.
    | An unconfigured/wrong value here doesn't silently under-protect either
    | — worst case is every visitor sharing one rate-limit bucket, not the
    | limiter being bypassable.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
