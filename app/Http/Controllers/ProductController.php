<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use App\Support\ProductImages;
use App\Support\Site;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalog) {}

    public function index(): View
    {
        return $this->stub('products.index');
    }

    /**
     * The pumps/valves/control/water routes are registered as literal
     * segments ahead of {slug} in routes/web.php, so this action only ever
     * receives real product slugs — never the 4 reserved category words.
     */
    public function show(Request $request): View
    {
        return $this->stub('products.show', ['slug' => $this->routeParam($request, 'slug')]);
    }

    public function pumps(): View
    {
        return $this->categoryPage(
            category: 'pumps',
            title: __('CategoryPage.allPumpsTitle'),
            currentRoute: '/products/pumps',
            defaultPanelId: 'fire-pump',
        );
    }

    public function valves(): View
    {
        return $this->stub('products.valves');
    }

    public function control(): View
    {
        return $this->stub('products.control');
    }

    public function water(): View
    {
        return $this->stub('products.water');
    }

    /**
     * Ports components/catalog/CatalogPage.tsx's CatalogPageView, shared by
     * every single-category catalog page (pumps here; valves/water/control
     * follow the same pattern in a later Stage 5 batch).
     */
    private function categoryPage(string $category, string $title, string $currentRoute, string $defaultPanelId): View
    {
        $locale = app()->getLocale();

        $panels = $this->catalog->buildCatalogPanels($category, $locale);
        $panelIds = array_column($panels, 'id');
        $resolvedDefault = in_array($defaultPanelId, $panelIds, true)
            ? $defaultPanelId
            : ($panelIds[0] ?? 'wastewater-submersible');

        $panelLabels = [];
        $heroDataByPanel = [];
        foreach ($panels as $panel) {
            $panelLabels[$panel['id']] = __('CatalogNav.'.($panel['id'] === 'valves' ? 'valvesFooterLabel' : $panel['id']));

            $firstProductSlug = $panel['section']['products'][0]['slug'] ?? null;
            $heroDataByPanel[$panel['id']] = [
                'images' => ProductImages::categoryPanelHeroImages()[$panel['id']] ?? null,
                'fallbackSrc' => $firstProductSlug
                    ? ProductImages::productImageSrc(['slug' => $firstProductSlug])
                    : '/assets/pump.png',
                'alt' => ProductImages::categoryPanelHeroAlt($panel['id']),
            ];
        }

        return view('pages.products.pumps', [
            'title' => $title,
            'intro' => Site::catalogIntro($locale),
            'panels' => $panels,
            'panelLabels' => $panelLabels,
            'heroDataByPanel' => $heroDataByPanel,
            'searchIndex' => $this->catalog->searchIndex($category, $locale),
            'currentRoute' => $currentRoute,
            'defaultPanelId' => $resolvedDefault,
            'searchPlaceholder' => __('CategoryPage.searchPlaceholderCategory'),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Common.products'), 'route' => 'products.index'],
                ['label' => $title],
            ],
        ]);
    }
}
