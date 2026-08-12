<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\Product;
use Illuminate\Contracts\View\View;

/** Ported from app/admin/(protected)/page.tsx + components/admin/StatCard.tsx. */
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalRequests' => ContactSubmission::query()->count(),
            'newRequests' => ContactSubmission::query()->where('status', 'new')->count(),
            'todayRequests' => ContactSubmission::query()->whereDate('created_at', today())->count(),
            'totalProducts' => Product::query()->count(),
        ]);
    }
}
