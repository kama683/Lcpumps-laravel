<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Route parameters must be read by name via Request::route(), never as
     * typed scalar method arguments. Laravel's controller-argument binding
     * for plain scalars is positional (see
     * Illuminate\Routing\ResolvesRouteDependencies::resolveMethodDependencies),
     * not name-matched — so on a "loc.*" route, {locale} occupies position 0
     * and would silently shift into a `show(string $slug)` parameter,
     * yielding the locale code instead of the slug. Request::route($name)
     * does a real named lookup and is immune to this.
     */
    protected function routeParam(Request $request, string $name): string
    {
        return (string) $request->route($name);
    }

    /**
     * Stage 4 placeholder: renders resources/views/pages/stub.blade.php so
     * routing/locale/params can be verified end-to-end before Stage 5 wires
     * real views. Delete this and every call site once Stage 5 is done.
     */
    protected function stub(string $routeName, array $params = []): View
    {
        return view('pages.stub', ['routeName' => $routeName, 'params' => $params]);
    }

    /**
     * Stage 7 placeholder for the Stage 8 admin JSON endpoints — registered
     * (behind admin.auth) now so the Stage 6 notification email's
     * admin.requests.show link, and admin.attachments.download, resolve to
     * something instead of 404ing. 501 Not Implemented, not 200, so it
     * can't be mistaken for a working response.
     */
    protected function stubJson(string $routeName, array $params = []): JsonResponse
    {
        return response()->json(['stub' => $routeName, 'params' => $params], 501);
    }
}
