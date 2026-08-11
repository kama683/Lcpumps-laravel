<?php

namespace App\Support\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

/**
 * Registers each route in $callback twice: once unprefixed (the default
 * locale, ru, matching the live site's unprefixed URLs) and once under
 * /{locale} for kk/en/zh — mirroring next-intl's localePrefix: "as-needed".
 *
 * A single {locale?} optional-segment route was deliberately rejected: it
 * can't express "ru has zero prefix, the other three require one" without
 * ambiguous matching against sibling static segments. Two physical route
 * registrations per pattern, matched in a fixed order, has no such
 * ambiguity.
 *
 * Route names stay locale-independent (e.g. "products.index") thanks to
 * App\Providers\AppServiceProvider's locale-aware UrlGenerator decorator,
 * which resolves "products.index" to the "loc.products.index" registration
 * (and fills in {locale}) whenever app()->getLocale() isn't the default.
 */
class LocalizedRoutes
{
    public const array PREFIXED_LOCALES = ['kk', 'en', 'zh'];

    public const string LOCALIZED_NAME_PREFIX = 'loc.';

    public static function register(Closure $callback): void
    {
        $callback();

        Route::prefix('{locale}')
            ->where(['locale' => implode('|', self::PREFIXED_LOCALES)])
            ->name(self::LOCALIZED_NAME_PREFIX)
            ->group($callback);
    }
}
