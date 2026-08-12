<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RateLimiterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Ported from app/admin/login/actions.ts + lib/auth/actions.ts. Checks run
 * in the source's order: rate limit, then empty fields, then credentials.
 * Error strings are hardcoded here rather than translated, same as the
 * source (they aren't in messages/*.json either) — the admin UI is
 * Russian-only by design, see the Stage 7 brief.
 *
 * Session guard replaces the source's stateless JWT (see config/auth.php's
 * "admin" guard) — a deliberate improvement: logout here actually revokes
 * the session server-side, which the source's cookie-only deleteSession()
 * cannot do.
 */
class AuthController extends Controller
{
    private const int LOGIN_MAX_ATTEMPTS = 5;

    private const int LOGIN_WINDOW_SECONDS = 15 * 60;

    public function showLoginForm(): View
    {
        return view('admin.login');
    }

    public function login(Request $request, RateLimiterService $rateLimiter): RedirectResponse
    {
        $ip = $request->ip() ?? 'unknown';
        $rateLimitKey = "login:{$ip}";

        if (! $rateLimiter->attempt($rateLimitKey, self::LOGIN_MAX_ATTEMPTS, self::LOGIN_WINDOW_SECONDS)) {
            return back()->withErrors(['login' => 'Слишком много попыток входа. Попробуйте снова через 15 минут.']);
        }

        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if ($username === '' || $password === '') {
            return back()->withInput(['username' => $username])
                ->withErrors(['login' => 'Введите логин и пароль.']);
        }

        if (! Auth::guard('admin')->attempt(['username' => $username, 'password' => $password])) {
            return back()->withInput(['username' => $username])
                ->withErrors(['login' => 'Неверный логин или пароль.']);
        }

        // Deliberate departure from the source: it spends an attempt on
        // successful logins too, so an admin logging in from a few devices
        // within 15 minutes can lock themselves out. This is a single-admin
        // panel, not a public form — resetting on success keeps the limiter
        // aimed at brute-forcing, not at legitimate use.
        $rateLimiter->clear($rateLimitKey);

        // Session fixation defense: issue a fresh session ID post-login.
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
