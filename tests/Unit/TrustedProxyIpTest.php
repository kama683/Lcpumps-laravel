<?php

namespace Tests\Unit;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Exercises config/trustedproxy.php's two states directly against the real
 * Illuminate\Http\Middleware\TrustProxies + Request::ip() mechanism, rather
 * than through a full HTTP round trip — this is specifically about proving
 * the IP-resolution contract Stage 6/7's rate limiters rely on.
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
}
