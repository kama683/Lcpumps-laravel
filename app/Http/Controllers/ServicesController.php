<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ServicesController extends Controller
{
    public function index(): View
    {
        return $this->stub('services.index');
    }
}
