<?php

use App\Support\Routing\LocalizedRoutes;
use Illuminate\Support\Facades\Route;

if (! function_exists('locale_url')) {
    /**
     * Build the URL for the given locale, defaulting to the current route
     * and its current parameters — this is what the language switcher and
     * (Stage 9) the per-page hreflang tags use to cross-link locales.
     * Ordinary same-locale links in Blade should keep using route(name),
     * which is already locale-aware — see LocaleAwareUrlGenerator.
     *
     * Deliberately swaps app()->getLocale() for the duration of the call
     * instead of duplicating LocaleAwareUrlGenerator's routing logic here:
     * that class always resolves against the *ambient* current locale, so
     * calling route() directly with a different target locale would still
     * generate a URL for the request's real locale, not the one asked for.
     */
    function locale_url(string $locale, ?string $routeName = null, array $parameters = []): string
    {
        $current = Route::current();
        $routeName ??= preg_replace('/^'.preg_quote(LocalizedRoutes::LOCALIZED_NAME_PREFIX, '/').'/', '', (string) $current?->getName());

        $parameters = array_merge($current?->parameters() ?? [], $parameters);
        unset($parameters['locale']);

        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            return route($routeName, $parameters);
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
