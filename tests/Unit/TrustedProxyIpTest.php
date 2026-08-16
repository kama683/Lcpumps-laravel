<?php

namespace Tests\Unit;

use App\Http\Middleware\NormalizeRealIpHeader;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Exercises config/trustedproxy.php's two states directly against the real
 * Illuminate\Http\Middleware\TrustProxies + Request::ip() mechanism, rather
 * than through a full HTTP round trip — this is specifically about proving
 * the IP-resolution contract Stage 6/7's rate limiters rely on. The
 * X-Real-IP-prefixed tests further down also chain in
 * App\Http\Middleware\NormalizeRealIpHeader, since that's what PS.kz's
 * actual proxy sends in production instead of X-Forwarded-For.
 */
class TrustedProxyIpTest extends TestCase
{
    protected function tearDown(): void
    {
        TrustProxies::flushState();
        parent::tearDown();
    }

    private function resolveIp(string $remoteAddr, ?string $forwardedFor, ?string $trustedProxiesConfig): ?string
    {
        config(['trustedproxy.proxies' => $trustedProxiesConfig]);

        $server = ['REMOTE_ADDR' => $remoteAddr];
        if ($forwardedFor !== null) {
            $server['HTTP_X_FORWARDED_FOR'] = $forwardedFor;
        }

        $request = Request::create('/admin/login', 'POST', [], [], [], $server);

        (new TrustProxies)->handle($request, fn ($r) => $r);

        return $request->ip();
    }

    public function test_x_forwarded_for_is_ignored_without_a_trusted_proxy_configured(): void
    {
        // The safe default (TRUSTED_PROXIES unset) — proves Stage 6/7's
        // switch to $request->ip() doesn't trust a bare header by default.
        $ip = $this->resolveIp('1.2.3.4', '9.9.9.9', null);

        $this->assertSame('1.2.3.4', $ip);
    }

    public function test_trusted_proxy_resolves_to_the_rightmost_untrusted_hop(): void
    {
        // Simulates nginx *appending* rather than replacing — the header
        // arrives as "<attacker-supplied>, <real-client-ip-nginx-added>",
        // exactly the shape the Stage 7 brief warns about for PS.kz.
        $ip = $this->resolveIp('1.2.3.4', '5.6.7.8, 9.9.9.9', '1.2.3.4');

        $this->assertSame('9.9.9.9', $ip);
    }

    public function test_attacker_controlled_prefix_cannot_change_the_resolved_ip(): void
    {
        $ipA = $this->resolveIp('1.2.3.4', '6.6.6.6, 9.9.9.9', '1.2.3.4');
        $ipB = $this->resolveIp('1.2.3.4', '7.7.7.7, 9.9.9.9', '1.2.3.4');

        $this->assertSame($ipA, $ipB);
        $this->assertSame('9.9.9.9', $ipA);
    }

    public function test_a_genuinely_different_real_client_still_resolves_differently(): void
    {
        $ip = $this->resolveIp('1.2.3.4', '6.6.6.6, 8.8.8.8', '1.2.3.4');

        $this->assertSame('8.8.8.8', $ip);
    }

    /**
     * PS.kz's actual proxy shape: REMOTE_ADDR is the proxy, and it sends
     * X-Real-IP (not X-Forwarded-For) with the visitor's IP. Runs the same
     * chain the real global middleware stack does — NormalizeRealIpHeader
     * first, then TrustProxies — rather than calling TrustProxies alone,
     * since that's the actual thing being proven here.
     */
    private function resolveIpViaRealIpHeader(string $remoteAddr, ?string $realIp, ?string $trustedProxiesConfig): ?string
    {
        config(['trustedproxy.proxies' => $trustedProxiesConfig]);

        $server = ['REMOTE_ADDR' => $remoteAddr];
        if ($realIp !== null) {
            $server['HTTP_X_REAL_IP'] = $realIp;
        }

        $request = Request::create('/admin/login', 'POST', [], [], [], $server);

        (new NormalizeRealIpHeader)->handle($request, function ($r) {
            (new TrustProxies)->handle($r, fn ($r) => $r);

            return response('');
        });

        return $request->ip();
    }

    public function test_x_real_ip_header_is_mapped_and_resolves_the_real_visitor(): void
    {
        $ip = $this->resolveIpViaRealIpHeader('195.210.46.99', '77.88.55.11', '195.210.46.99');

        $this->assertSame('77.88.55.11', $ip);
    }

    public function test_x_real_ip_is_ignored_without_a_trusted_proxy_configured(): void
    {
        $ip = $this->resolveIpViaRealIpHeader('195.210.46.99', '77.88.55.11', null);

        $this->assertSame('195.210.46.99', $ip);
    }

    /**
     * PS.kz's proxy is confirmed (via a phpinfo dump from the real server)
     * to never set X-Forwarded-For at all — only X-Real-IP. So if both
     * headers show up on the same request, X-Forwarded-For can only be
     * attacker-supplied (sent directly, trying to slip past the X-Real-IP
     * mapping) — X-Real-IP must win, not the other way around. An earlier
     * version of NormalizeRealIpHeader got this backwards (preserved a
     * pre-existing X-Forwarded-For on the theory that it might be a real
     * chain), which is exactly the gap this test now guards against.
     */
    public function test_x_real_ip_overrides_a_spoofed_x_forwarded_for_header(): void
    {
        config(['trustedproxy.proxies' => '195.210.46.99']);

        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '195.210.46.99',
            'HTTP_X_FORWARDED_FOR' => '9.9.9.9',
            'HTTP_X_REAL_IP' => '77.88.55.11',
        ]);

        (new NormalizeRealIpHeader)->handle($request, function ($r) {
            (new TrustProxies)->handle($r, fn ($r) => $r);

            return response('');
        });

        $this->assertSame('77.88.55.11', $request->ip());
    }

    /**
     * The actual production combination: TRUSTED_PROXIES=* (trust the
     * direct TCP peer unconditionally — see DEPLOY.md for why that's safe
     * specifically because PHP-FPM has no ingress path except through this
     * one proxy) means whatever ends up in X-Forwarded-For after
     * NormalizeRealIpHeader runs is taken as-is. If a client sends
     * X-Forwarded-For directly, with no X-Real-IP at all, that header must
     * be stripped rather than trusted — under "*" there is no proxy-IP
     * check left to catch it otherwise, this middleware is the only thing
     * standing between an attacker's raw header and $request->ip().
     */
    public function test_spoofed_x_forwarded_for_is_stripped_under_trust_all_when_no_x_real_ip_present(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '195.210.46.99',
            'HTTP_X_FORWARDED_FOR' => '6.6.6.6',
        ]);

        (new NormalizeRealIpHeader)->handle($request, function ($r) {
            (new TrustProxies)->handle($r, fn ($r) => $r);

            return response('');
        });

        // Falls back to REMOTE_ADDR (the proxy) — not great, not the real
        // visitor either, but crucially NOT the attacker-chosen 6.6.6.6.
        $this->assertSame('195.210.46.99', $request->ip());
    }

    public function test_x_real_ip_still_resolves_correctly_under_trust_all(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $request = Request::create('/admin/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '195.210.46.99',
            'HTTP_X_REAL_IP' => '77.88.55.11',
        ]);

        (new NormalizeRealIpHeader)->handle($request, function ($r) {
            (new TrustProxies)->handle($r, fn ($r) => $r);

            return response('');
        });

        $this->assertSame('77.88.55.11', $request->ip());
    }
}
