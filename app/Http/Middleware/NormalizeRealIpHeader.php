<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PS.kz's Apache proxy sends the real visitor IP as X-Real-IP, not the
 * X-Forwarded-For that Illuminate\Http\Middleware\TrustProxies (and
 * config/trustedproxy.php) are built around — and confirmed via a phpinfo
 * dump from the real server, PS.kz's proxy **never sends
 * X-Forwarded-For at all**. TRUSTED_PROXIES is set to "*" in production
 * (trust the direct TCP peer unconditionally — safe here specifically
 * because PHP-FPM has no ingress path except through this one proxy, see
 * config/trustedproxy.php), which makes this middleware's behavior
 * towards X-Forwarded-For the *entire* spoofing boundary: whatever ends
 * up in that header is what TrustProxies treats as the forwarding chain.
 *
 * Given that, this middleware always OVERWRITES X-Forwarded-For from
 * X-Real-IP when present, and STRIPS it entirely when X-Real-IP is
 * absent — it never leaves a pre-existing X-Forwarded-For value in place.
 * An earlier version only filled X-Forwarded-For when it wasn't already
 * set, on the theory that a "real" forwarding chain should never be
 * overwritten — but since PS.kz's proxy is confirmed to never set
 * X-Forwarded-For itself, there is no legitimate scenario where that
 * header arrives already populated; any X-Forwarded-For present at this
 * point can only be attacker-supplied, sent directly to bypass the
 * X-Real-IP mapping and inject an arbitrary IP straight into
 * $request->ip(). Discarding it is what actually closes that hole —
 * "don't overwrite a chain that might be real" was the wrong instinct
 * for a proxy that's confirmed to never produce one.
 *
 * Prepended to the global middleware stack (bootstrap/app.php) so it runs
 * before TrustProxies.
 */
class NormalizeRealIpHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->headers->has('X-Real-IP')) {
            $realIp = $request->headers->get('X-Real-IP');
            $request->headers->set('X-Forwarded-For', $realIp);
            $request->server->set('HTTP_X_FORWARDED_FOR', $realIp);
        } else {
            $request->headers->remove('X-Forwarded-For');
            $request->server->remove('HTTP_X_FORWARDED_FOR');
        }

        return $next($request);
    }
}
