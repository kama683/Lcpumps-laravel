<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [SetLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Two-tier 404, mirroring the source project's app/[locale]/not-found.tsx
        // (chrome, shown when a matched route's controller calls abort(404) on a
        // bad slug) vs app/global-not-found.tsx (no chrome, for paths that never
        // matched any route at all — locale was never resolved for these).
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->route() !== null) {
                return response()->view('errors.404-in-locale', [], 404);
            }

            return null; // falls through to the default errors/404.blade.php
        });
    })->create();
