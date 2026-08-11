<?php

namespace App\Providers;

use App\Support\Routing\LocaleAwareUrlGenerator;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('url', function (UrlGeneratorContract $url, $app) {
            $generator = new LocaleAwareUrlGenerator(
                $app['router']->getRoutes(),
                $app['request'],
                $app['config']['app.asset_url']
            );

            // Same resolvers Illuminate\Routing\RoutingServiceProvider attaches to
            // the default instance, reapplied so signedRoute()/temporarySignedRoute()
            // keep working on the swapped-in generator.
            $generator->setSessionResolver(fn () => $app['session'] ?? null);
            $generator->setKeyResolver(fn () => [
                $app['config']->get('app.key'),
                ...($app['config']->get('app.previous_keys') ?? []),
            ]);

            return $generator;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
