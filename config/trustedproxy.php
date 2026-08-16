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
    | TCP peer is.
    |
    | Production value is "*" — deliberate, not a shortcut taken to avoid
    | figuring out the real proxy IP. PS.kz's PHP-FPM has no ingress path
    | except through their one Apache proxy (confirmed: it's shared
    | hosting, PHP-FPM isn't independently reachable), so restricting to a
    | specific proxy IP wouldn't add real protection here — an attacker
    | still can't reach PHP-FPM from anywhere else to *pretend* to be that
    | proxy. What "*" actually depends on for safety is
    | App\Http\Middleware\NormalizeRealIpHeader (prepended before this
    | middleware in bootstrap/app.php): it always overwrites
    | X-Forwarded-For from X-Real-IP when present, and strips
    | X-Forwarded-For entirely when X-Real-IP is absent — confirmed via a
    | phpinfo dump from the real server that PS.kz's proxy sends X-Real-IP
    | and never X-Forwarded-For, so any X-Forwarded-For arriving on its own
    | can only be attacker-supplied, never a legitimate chain. See that
    | middleware's docblock and TrustedProxyIpTest for the tests proving
    | this, and DEPLOY.md's TrustProxies section for the post-deploy
    | verification (spoofed-header curl test + a real-phone-over-mobile-data
    | check).
    |
    | Left empty in local dev: Symfony/Laravel's default behavior is to
    | ignore X-Forwarded-For entirely and report the raw TCP peer from
    | $request->ip() — safe, not a bug, and exactly why
    | ContactSubmissionHandler (Stage 6) and Admin\AuthController (Stage 7)
    | both call $request->ip() for their rate-limit keys instead of parsing
    | forwarding headers by hand: this one setting controls IP-spoofing
    | resistance for both, instead of duplicating trust logic per call site.
    | An unconfigured/wrong value here doesn't silently under-protect either
    | — worst case is every visitor sharing one rate-limit bucket, not the
    | limiter being bypassable.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
