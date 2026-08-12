<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/** Stage 8 placeholder (product CRUD) — routes exist now, behind admin.auth, so nothing links to a 404. */
class ProductController extends Controller
{
    public function index(): View
    {
        return $this->stub('admin.products.index');
    }

    public function uploadImage(): JsonResponse
    {
        return $this->stubJson('admin.products.image.upload');
    }
}
