<?php

use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Support\Routing\LocalizedRoutes;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
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

        $middleware->alias([
            'admin.auth' => EnsureAdminAuthenticated::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
        ]);
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

        // ValidatePostSize is global middleware — it runs before routing and
        // before the "web" group (session, CSRF), so by the time this fires
        // there is no session to flash ->withErrors() into and no matched
        // route to inspect. Only the contact form accepts uploads large
        // enough to hit this, so it's detected by raw path instead; the
        // locale is read from the path too, since SetLocale (appended to
        // the "web" group) hasn't run either.
        //
        // contactFormError carries the bare error CODE ("totalSizeExceeded"),
        // never translated text — a query string is attacker-writable
        // (anyone can visit /contact?contactFormError=<anything> directly,
        // no oversized upload needed), so the value must never be echoed as
        // the message itself. The Blade contact page must check the code
        // against the same whitelist Contact.form.errors.* defines and
        // render __('Contact.form.errors.'.$code) — see
        // ContactController::submit's doc block.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $contactPaths = ['contact', ...array_map(fn ($locale) => "{$locale}/contact", LocalizedRoutes::PREFIXED_LOCALES)];

            if (! $request->isMethod('post') || ! $request->is(...$contactPaths)) {
                return null;
            }

            $locale = in_array($request->segment(1), LocalizedRoutes::PREFIXED_LOCALES, true)
                ? $request->segment(1)
                : config('app.locale');

            return redirect(locale_url($locale, 'contact.index', ['contactFormError' => 'totalSizeExceeded']));
        });
    })->create();
