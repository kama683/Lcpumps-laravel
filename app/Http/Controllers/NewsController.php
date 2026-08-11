<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return $this->stub('news.index');
    }
}
