<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PS.kz's Apache proxy sends the real visitor IP as X-Real-IP, not the
 * X-Forwarded-For that Illuminate\Http\Middleware\TrustProxies (and
 * config/trustedproxy.php, and TrustedProxyIpTest) are all built around.
 * Left unmapped, TRUSTED_PROXIES would have nothing to read and
 * $request->ip() would keep resolving to REMOTE_ADDR — the proxy's own
 * IP, not the visitor's — silently defeating the point of configuring
 * trusted proxies at all (see DEPLOY.md's TrustProxies section for why
 * that specifically breaks the contact-form/admin-login rate limiters).
 *
 * Prepended to the global middleware stack (bootstrap/app.php) so it runs
 * before TrustProxies. Only fills X-Forwarded-For when the proxy didn't
 * already send one, so a real forwarding chain is never overwritten —
 * and TrustProxies' own (tested) trusted-proxy/spoofing logic is
 * completely unchanged, this only fixes the header name mismatch.
 */
class NormalizeRealIpHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('X-Forwarded-For') && $request->headers->has('X-Real-IP')) {
            $realIp = $request->headers->get('X-Real-IP');
            $request->headers->set('X-Forwarded-For', $realIp);
            $request->server->set('HTTP_X_FORWARDED_FOR', $realIp);
        }

        return $next($request);
    }
}
