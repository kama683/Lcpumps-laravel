<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use Illuminate\Contracts\View\View;

class SolutionsController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): View
    {
        $locale = app()->getLocale();

        return view('pages.solutions.index', [
            'metaTitle' => __('PageMetadata.solutions.title'),
            'products' => $this->catalog->waterProducts($locale),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Solutions.breadcrumbLabel')],
            ],
        ]);
    }
}
