<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Alias "admin.auth". Deliberately not Laravel's built-in `auth` middleware:
 * that redirects guests to a route named "login" (doesn't exist here — this
 * app's is "admin.login") and its JSON-vs-redirect split depends on
 * $request->expectsJson(), which isn't reliably true for a plain browser
 * GET to /api/admin/* without an explicit Accept header — exactly the "500
 * or redirect to nowhere" trap called out for this stage. Deciding by path
 * prefix here is unambiguous: every /api/admin/* route is JSON-only by
 * contract (see routes/web.php), everything else under /admin is an
 * HTML page.
 */
class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        if ($request->is('api/admin/*')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return redirect()->route('admin.login');
    }
}
