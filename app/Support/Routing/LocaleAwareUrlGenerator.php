<?php

namespace App\Support\Routing;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;
use Illuminate\Support\Arr;

/**
 * Makes the global route() helper locale-independent: route('products.index')
 * resolves to the /{locale}/products URL whenever the current app locale
 * isn't the default (ru), without callers ever naming the "loc." variant.
 *
 * Registered in AppServiceProvider via Container::extend('url', ...), which
 * runs after Illuminate\Routing\RoutingServiceProvider has already attached
 * the session/key resolvers Laravel needs for things like signedRoute() —
 * this subclass is constructed the same way the framework builds the
 * original instance so that configuration isn't lost.
 */
class LocaleAwareUrlGenerator extends BaseUrlGenerator
{
    public function route($name, $parameters = [], $absolute = true)
    {
        $locale = app()->getLocale();

        if ($locale !== config('app.fallback_locale')) {
            $localizedName = LocalizedRoutes::LOCALIZED_NAME_PREFIX.$name;

            if ($this->routes->hasNamedRoute($localizedName)) {
                $parameters = Arr::wrap($parameters);
                $parameters += ['locale' => $locale];

                return parent::route($localizedName, $parameters, $absolute);
            }
        }

        return parent::route($name, $parameters, $absolute);
    }
}
