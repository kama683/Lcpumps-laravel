<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(string $username = 'admin', string $password = 'correct-password'): AdminUser
    {
        return AdminUser::create([
            'username' => $username,
            'password_hash' => Hash::make($password),
        ]);
    }

    public function test_successful_login_authenticates_and_redirects_to_dashboard(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        $response = $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::guard('admin')->check());
        $response->assertSessionHasNoErrors();
    }

    public function test_login_regenerates_the_session_id(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        $this->withSession([]);
        $this->get(route('admin.login'));
        $idBefore = $this->app['session']->getId();

        $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'correct-password',
        ]);

        $idAfter = $this->app['session']->getId();
        $this->assertNotSame($idBefore, $idAfter);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        $response = $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['login' => 'Неверный логин или пароль.']);
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_nonexistent_username_is_rejected_with_the_same_message(): void
    {
        $response = $this->post(route('admin.login.attempt'), [
            'username' => 'ghost',
            'password' => 'whatever',
        ]);

        $response->assertSessionHasErrors(['login' => 'Неверный логин или пароль.']);
    }

    public function test_empty_fields_are_rejected(): void
    {
        $response = $this->post(route('admin.login.attempt'), [
            'username' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['login' => 'Введите логин и пароль.']);
    }

    public function test_username_is_flashed_but_password_never_is(): void
    {
        $this->post(route('admin.login.attempt'), [
            'username' => 'someone',
            'password' => 'wrong',
        ]);

        $this->assertSame('someone', old('username'));
        $this->assertNull(old('password'));
    }

    public function test_rate_limit_blocks_after_five_attempts_from_the_same_ip(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.attempt'), [
                'username' => 'admin',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors(['login' => 'Неверный логин или пароль.']);
        }

        $response = $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors(['login' => 'Слишком много попыток входа. Попробуйте снова через 15 минут.']);
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_successful_login_resets_the_rate_limit_counter(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('admin.login.attempt'), [
                'username' => 'admin',
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('admin.logout'));

        // If the counter hadn't been cleared on success, this 5th
        // post-login attempt would already be the 6th overall and get
        // rateLimited instead of the ordinary wrong-password message.
        $response = $this->post(route('admin.login.attempt'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['login' => 'Неверный логин или пароль.']);
    }

    public function test_spoofed_x_forwarded_for_does_not_evade_the_rate_limit(): void
    {
        $this->makeAdmin('admin', 'correct-password');

        for ($i = 0; $i < 5; $i++) {
            $this->withHeaders(['X-Forwarded-For' => "10.0.0.{$i}"])
                ->post(route('admin.login.attempt'), [
                    'username' => 'admin',
                    'password' => 'wrong-password',
                ]);
        }

        // TRUSTED_PROXIES is unset in this environment (the safe default —
        // see config/trustedproxy.php), so $request->ip() must ignore
        // X-Forwarded-For entirely and keep resolving to the same TCP
        // peer for every request above, regardless of the header.
        $response = $this->withHeaders(['X-Forwarded-For' => '10.0.0.99'])
            ->post(route('admin.login.attempt'), [
                'username' => 'admin',
                'password' => 'correct-password',
            ]);

        $response->assertSessionHasErrors(['login' => 'Слишком много попыток входа. Попробуйте снова через 15 минут.']);
    }

    public function test_guest_visiting_admin_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_hitting_admin_api_gets_json_401(): void
    {
        $response = $this->getJson('/api/admin/attachments/some-id');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_guest_hitting_admin_api_without_json_accept_header_still_gets_json_401(): void
    {
        // Regression guard for the trap called out in the Stage 7 brief:
        // Laravel's built-in auth middleware decides JSON-vs-redirect via
        // expectsJson(), which a plain browser GET can fail even for an
        // API path — the custom admin.auth middleware decides by path
        // prefix instead, so this must be 401 JSON even without an
        // Accept: application/json header.
        $response = $this->get('/api/admin/attachments/some-id');

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $admin = $this->makeAdmin('admin', 'correct-password');
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_clears_the_session_and_redirects_to_login(): void
    {
        $admin = $this->makeAdmin('admin', 'correct-password');
        $this->actingAs($admin, 'admin');

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin')->check());
    }

    // Stage 7's coverage here (routes resolve behind admin.auth, unauth
    // gets redirected/401) is superseded by Stage 8's real controllers —
    // see ProductAdminTest::test_product_admin_routes_require_authentication
    // and RequestAdminTest::test_request_admin_routes_require_authentication,
    // which exercise the same routes with real (not stub) responses.
}
