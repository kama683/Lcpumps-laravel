<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SolutionsController extends Controller
{
    public function index(): View
    {
        return $this->stub('solutions.index');
    }
}
