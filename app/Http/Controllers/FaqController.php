<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return $this->stub('faq.index');
    }
}
