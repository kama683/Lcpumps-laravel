<?php

namespace App\Http\Middleware;

use App\Support\Routing\LocalizedRoutes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current request's locale from the matched route: "loc.*"
 * routes carry an explicit {locale} parameter (kk/en/zh), everything else
 * is the default locale (ru), matching next-intl's unprefixed-default
 * behavior. Runs for every request in the "web" group, including ones
 * that end in a controller-triggered 404 — only truly unmatched paths
 * (no route at all) skip this middleware, which is what keeps the
 * "no-chrome" 404 tier locale-less. See bootstrap/app.php for that split.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $locale = str_starts_with((string) $routeName, LocalizedRoutes::LOCALIZED_NAME_PREFIX)
            ? $request->route('locale')
            : config('app.fallback_locale');

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
