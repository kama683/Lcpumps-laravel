<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Stage 8 placeholder (contact-submission review) — routes exist now, behind admin.auth, so the Stage 6 notification email's link resolves. */
class RequestController extends Controller
{
    public function index(): View
    {
        return $this->stub('admin.requests.index');
    }

    public function show(Request $request): View
    {
        return $this->stub('admin.requests.show', ['id' => $this->routeParam($request, 'id')]);
    }
}
