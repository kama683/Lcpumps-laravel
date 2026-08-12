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
        return $this->categoryPage(
            category: null,
            metaTitle: __('CategoryPage.allPumpsTitle'),
            currentRoute: '/products',
            defaultPanelId: 'fire-pump',
            breadcrumb: [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Common.products')],
            ],
            searchPlaceholder: __('CategoryPage.searchPlaceholderAll'),
        );
    }

    /**
     * The pumps/valves/control/water routes are registered as literal
     * segments ahead of {slug} in routes/web.php, so this action only ever
     * receives real product slugs — never the 4 reserved category words in
     * practice, but Site::RESERVED_PRODUCT_SLUGS is still checked to match
     * the source's defensive guard exactly.
     */
    public function show(Request $request): View
    {
        $slug = $this->routeParam($request, 'slug');
        $locale = app()->getLocale();

        if (in_array($slug, Site::RESERVED_PRODUCT_SLUGS, true)) {
            abort(404);
        }

        $allProducts = $this->catalog->allProducts($locale);
        $product = $allProducts->firstWhere('slug', $slug);
        if (! $product) {
            abort(404);
        }

        $sameSection = $allProducts->filter(
            fn ($p) => $p->sectionId && $p->sectionId === $product->sectionId && $p->slug !== $product->slug
        );
        $related = ($sameSection->isNotEmpty()
            ? $sameSection
            : $allProducts->filter(fn ($p) => $p->category === $product->category && $p->slug !== $product->slug)
        )->take(3)->values();

        $categoryRoute = match ($product->category) {
            'pumps' => 'products.index',
            default => 'products.'.$product->category,
        };

        $hasModelCode = $product->hasModelCode;
        $imageSrc = ProductImages::productImageSrc(['slug' => $product->slug, 'image' => $product->image]);
        $imageAlt = $hasModelCode
            ? "{$product->code} — {$product->name} product photo"
            : "{$product->name} product photo";

        return view('pages.products.show', [
            'metaTitle' => $hasModelCode ? "{$product->code} — {$product->name}" : $product->name,
            'product' => $product,
            'hasModelCode' => $hasModelCode,
            'imageSrc' => $imageSrc,
            'imageAlt' => $imageAlt,
            'related' => $related,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Common.products'), 'route' => 'products.index'],
                ['label' => __('ProductDetail.category'), 'route' => $categoryRoute],
                ['label' => $hasModelCode ? $product->code : $product->name],
            ],
        ]);
    }

    public function pumps(): View
    {
        return $this->categoryPage(
            category: 'pumps',
            metaTitle: __('CategoryPage.pumpsTitle'),
            currentRoute: '/products/pumps',
            defaultPanelId: 'fire-pump',
        );
    }

    public function valves(): View
    {
        return $this->categoryPage(
            category: 'valves',
            metaTitle: __('CategoryPage.valvesTitle'),
            currentRoute: '/products/valves',
            defaultPanelId: 'valves',
        );
    }

    /**
     * No CATALOG_NAV item has category "control" (verified against the
     * source: Site::CATALOG_NAV's 8 entries are only pumps/water/valves) —
     * buildCatalogPanels('control', ...) is genuinely empty on the live
     * site too, confirmed by screenshot. The page still renders (hero
     * falls back to the hardcoded "wastewater-submersible" panel id, H1
     * falls back to $metaTitle, product grid area is empty) — not a bug
     * to route around, the live /products/control page really looks like
     * this today.
     */
    public function control(): View
    {
        return $this->categoryPage(
            category: 'control',
            metaTitle: __('CategoryPage.controlTitle'),
            currentRoute: '/products/control',
            defaultPanelId: 'control-panel',
        );
    }

    public function water(): View
    {
        return $this->categoryPage(
            category: 'water',
            metaTitle: __('CategoryPage.waterTitle'),
            currentRoute: '/products/water',
            defaultPanelId: 'water-supply-complete',
        );
    }

    /**
     * Ports components/catalog/CatalogPage.tsx's CatalogPageView + the
     * "title" text (always CategoryPage.allPumpsTitle) that all five
     * catalog-listing pages pass into CatalogPageShell — that's separate
     * from the per-page <title> tag ($metaTitle), which is page-specific
     * (see each generateMetadata() in the source's app/[locale]/products/*).
     */
    private function categoryPage(
        ?string $category,
        string $metaTitle,
        string $currentRoute,
        string $defaultPanelId,
        ?array $breadcrumb = null,
        ?string $searchPlaceholder = null,
    ): View {
        $locale = app()->getLocale();
        $title = __('CategoryPage.allPumpsTitle');

        $panels = $this->catalog->buildCatalogPanels($category, $locale);
        $panelIds = array_column($panels, 'id');
        $resolvedDefault = in_array($defaultPanelId, $panelIds, true)
            ? $defaultPanelId
            : ($panelIds[0] ?? 'wastewater-submersible');

        $panelLabels = [];
        $heroDataByPanel = [];
        foreach ($panels as $panel) {
            $panelLabels[$panel['id']] = __('CatalogNav.'.($panel['id'] === 'valves' ? 'valvesFooterLabel' : $panel['id']));
            $heroDataByPanel[$panel['id']] = $this->heroData($panel['id'], $panel['section']['products'][0]['slug'] ?? null);
        }

        // Hero image is looked up independently of $panels (CATEGORY_PANEL_HERO_IMAGES
        // is a global map keyed by panel id) — matters for /products/control, where
        // $resolvedDefault falls back to "wastewater-submersible", a panel that isn't
        // even in $panels, yet the source still renders its hero photo. H1 falls back
        // to $title in that same case (matches CatalogPageShell's
        // `activePanelData ? tCatalogNav(...) : title`).
        if (! isset($heroDataByPanel[$resolvedDefault])) {
            $heroDataByPanel[$resolvedDefault] = $this->heroData($resolvedDefault, null);
        }

        // Unlike $heading (H1, falls back to $title when the resolved panel
        // isn't in $panels), the mobile category-picker button label always
        // resolves via a direct CatalogNav translation regardless of $panels
        // — matches CatalogLayout's `tCatalogNav(activePanel === "valves" ?
        // "valvesFooterLabel" : activePanel)`, which never checks $panels.
        $defaultPanelLabel = __('CatalogNav.'.($resolvedDefault === 'valves' ? 'valvesFooterLabel' : $resolvedDefault));

        return view('pages.products.catalog', [
            'title' => $title,
            'metaTitle' => $metaTitle,
            'intro' => Site::catalogIntro($locale),
            'panels' => $panels,
            'panelLabels' => $panelLabels,
            'heading' => $panelLabels[$resolvedDefault] ?? $title,
            'defaultPanelLabel' => $defaultPanelLabel,
            'heroDataByPanel' => $heroDataByPanel,
            'searchIndex' => $this->catalog->searchIndex($category, $locale),
            'currentRoute' => $currentRoute,
            'defaultPanelId' => $resolvedDefault,
            'searchPlaceholder' => $searchPlaceholder ?? __('CategoryPage.searchPlaceholderCategory'),
            'breadcrumb' => $breadcrumb ?? [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Common.products'), 'route' => 'products.index'],
                ['label' => $title],
            ],
        ]);
    }

    private function heroData(string $panelId, ?string $firstProductSlug): array
    {
        return [
            'images' => ProductImages::categoryPanelHeroImages()[$panelId] ?? null,
            'fallbackSrc' => $firstProductSlug
                ? ProductImages::productImageSrc(['slug' => $firstProductSlug])
                : '/assets/pump.png',
            'alt' => ProductImages::categoryPanelHeroAlt($panelId),
        ];
    }
}
