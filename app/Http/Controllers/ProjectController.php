<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): View
    {
        return $this->stub('projects.index');
    }

    // Note: singular URL segment ("/project/{slug}") for the show route —
    // matches the live, indexed site exactly; not a typo.
    public function show(Request $request): View
    {
        return $this->stub('projects.show', ['slug' => $this->routeParam($request, 'slug')]);
    }
}
