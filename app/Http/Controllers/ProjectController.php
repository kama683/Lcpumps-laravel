<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use App\Services\ProjectsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): View
    {
        $locale = app()->getLocale();
        $data = ProjectsRepository::forLocale($locale);

        return view('pages.projects.index', [
            'metaTitle' => __('PageMetadata.projects.title'),
            'projectsData' => $data,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Nav.projects')],
            ],
        ]);
    }

    // Note: singular URL segment ("/project/{slug}") for the show route —
    // matches the live, indexed site exactly; not a typo.
    public function show(Request $request): View
    {
        $slug = $this->routeParam($request, 'slug');
        $locale = app()->getLocale();

        $project = ProjectsRepository::bySlug($slug, $locale);
        if (! $project) {
            abort(404);
        }

        $projectsData = ProjectsRepository::forLocale($locale);
        $featured = $projectsData['featured'];
        $index = collect($featured)->search(fn ($p) => $p['slug'] === $slug);
        $prev = $index > 0 ? $featured[$index - 1] : null;
        $next = $index < count($featured) - 1 ? $featured[$index + 1] : null;

        $equipmentProducts = $this->catalog->productsBySlugs($project['equipmentSlugs'] ?? [], $locale);

        return view('pages.projects.show', [
            'metaTitle' => $project['name'],
            'project' => $project,
            'featured' => $featured,
            'prev' => $prev,
            'next' => $next,
            'equipmentProducts' => $equipmentProducts,
            'currentSlug' => $slug,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Nav.projects'), 'route' => 'projects.index'],
                ['label' => $project['name']],
            ],
        ]);
    }
}
