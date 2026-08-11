<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use App\Services\ProjectsRepository;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): View
    {
        $locale = app()->getLocale();

        return view('pages.home', [
            'heroSlides' => Site::HERO_SLIDES,
            'homeStats' => Site::homeStats($locale),
            'featuredProducts' => $this->catalog->productsBySlugs(Site::HOME_FEATURED_PRODUCTS, $locale),
            'featuredProjects' => array_slice(ProjectsRepository::forLocale($locale)['featured'], 0, 6),
        ]);
    }
}
