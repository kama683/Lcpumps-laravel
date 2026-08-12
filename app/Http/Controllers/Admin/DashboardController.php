<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/** Stage 7 placeholder — real content (product/request summaries etc.) is Stage 8. */
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', ['username' => Auth::guard('admin')->user()->username]);
    }
}
