<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ApplicationsController extends Controller
{
    public function index(): View
    {
        return $this->stub('applications.index');
    }

    public function waterWastewater(): View
    {
        return $this->stub('applications.water-wastewater');
    }
}
