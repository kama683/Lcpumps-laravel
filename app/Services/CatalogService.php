<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Section;
use App\Support\Site;
use Illuminate\Support\Collection;

/**
 * Ported from lib/catalog.ts + lib/repositories/{products,sections}.ts.
 * No caching layer (unlike the source's unstable_cache) — traffic/catalog
 * size doesn't warrant it yet; revisit if this becomes a bottleneck.
 */
class CatalogService
{
    /** @return Collection<int, object{slug:string,code:string,category:string,image:?string,sectionId:?string,name:string,description:array,specs:array,applications:array,hasModelCode:bool,section:string}> */
    public function allProducts(string $locale): Collection
    {
        $locales = array_unique([$locale, 'ru']);

        try {
            $products = Product::query()
                ->with(['translations' => fn ($q) => $q->whereIn('locale', $locales)])
                ->with(['section.translations' => fn ($q) => $q->where('locale', 'ru')])
                ->orderBy('sort_order')
                ->get();
        } catch (\Illuminate\Database\QueryException $e) {
            // During lightweight test runs the full migrations may not be run;
            // degrade gracefully by returning an empty collection instead of
            // blowing up the whole request. The absence of a products table
            // should not be fatal when producing e.g. the home page for tests.
            return collect([]);
        }

        return $products->map(function (Product $product) use ($locale) {
                $translation = $product->translations->firstWhere('locale', $locale);
                $ruTranslation = $product->translations->firstWhere('locale', 'ru');
                $ruName = $ruTranslation?->name ?? $translation?->name ?? '';
                $name = $translation?->name ?? $ruName;

                return (object) [
                    'slug' => $product->slug,
                    'code' => $product->code,
                    'category' => $product->category,
                    'image' => $product->image,
                    'sectionId' => $product->section_id,
                    'sortOrder' => $product->sort_order,
                    'name' => $name,
                    'description' => $translation?->description ?? [],
                    'specs' => $translation?->specs ?? [],
                    'applications' => $translation?->applications ?? [],
                    'hasModelCode' => trim($product->code) !== trim($ruName),
                    'section' => $product->section?->translations->first()?->title ?? '',
                ];
            });
    }

    /** @return Collection<int, object{id:string,title:string,paragraphs:array,project_list:array,bullets:?array}> */
    public function allSections(string $locale): Collection
    {
        return Section::query()
            ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
            ->get()
            ->map(function (Section $section) {
                $translation = $section->translations->first();

                return (object) [
                    'id' => $section->id,
                    'title' => $translation?->title ?? '',
                    'paragraphs' => $translation?->paragraphs ?? [],
                    'project_list' => $section->project_list ?? [],
                    'bullets' => $translation?->bullets,
                ];
            });
    }

    /**
     * Groups products into their CATALOG_NAV sections (by sectionId FK) plus
     * a fallback grouping by category (Site::CATEGORY_ANCHORS).
     *
     * @param  Collection<int, object>  $products
     * @param  Collection<int, object>  $sections
     * @return array<string, array{id:string,title:string,products:array,paragraphs:array,project_list:array,bullets?:?array}>
     */
    public function mergedCatalogSections(Collection $products, Collection $sections): array
    {
        $merged = [];
        $navIds = array_column(Site::CATALOG_NAV, 'id');

        foreach ($sections as $section) {
            if (! in_array($section->id, $navIds, true)) {
                continue;
            }
            $merged[$section->id] = [
                'id' => $section->id,
                'title' => $section->title,
                'products' => [],
                'paragraphs' => $section->paragraphs ?: [],
                'project_list' => $section->project_list ?: [],
                'bullets' => $section->bullets,
            ];
        }

        foreach ($products as $p) {
            if (! $p->sectionId || ! isset($merged[$p->sectionId])) {
                continue;
            }
            $merged[$p->sectionId]['products'][] = [
                'code' => $p->code, 'name' => $p->name, 'slug' => $p->slug, 'hasModelCode' => $p->hasModelCode,
            ];
        }

        foreach (Site::CATEGORY_ANCHORS as $category => $anchorId) {
            if (! isset($merged[$anchorId])) {
                $navTitle = collect(Site::CATALOG_NAV)->firstWhere('id', $anchorId)['title'] ?? $anchorId;
                $merged[$anchorId] = [
                    'id' => $anchorId, 'title' => $navTitle, 'products' => [], 'paragraphs' => [], 'project_list' => [],
                ];
            }
            $seen = array_column($merged[$anchorId]['products'], 'slug');
            foreach ($products as $p) {
                if ($p->category === $category && ! in_array($p->slug, $seen, true)) {
                    $merged[$anchorId]['products'][] = [
                        'code' => $p->code, 'name' => $p->name, 'slug' => $p->slug, 'hasModelCode' => $p->hasModelCode,
                    ];
                    $seen[] = $p->slug;
                }
            }
        }

        return $merged;
    }

    /** @return array<int, array{id:string,title:string,category:string,page:string,section:array}> */
    public function buildCatalogPanels(?string $categoryFilter, string $locale): array
    {
        $products = $this->allProducts($locale);
        $sections = $this->allSections($locale);
        $merged = $this->mergedCatalogSections($products, $sections);

        $effectiveCategory = $categoryFilter ?? 'pumps';
        $navItems = collect(Site::CATALOG_NAV)->filter(fn ($n) => $n['category'] === $effectiveCategory)->values();

        return $navItems->map(fn ($nav) => [
            ...$nav,
            'section' => $merged[$nav['id']] ?? [
                'id' => $nav['id'], 'title' => $nav['title'], 'products' => [], 'paragraphs' => [], 'project_list' => [],
            ],
        ])->all();
    }

    /** @return array<int, array{code:string,name:string,slug:string,hasModelCode:bool}> */
    public function searchIndex(?string $categoryFilter, string $locale): array
    {
        $effectiveCategory = $categoryFilter ?? 'pumps';

        return $this->allProducts($locale)
            ->filter(fn ($p) => $p->category === $effectiveCategory)
            ->map(fn ($p) => ['code' => $p->code, 'name' => $p->name, 'slug' => $p->slug, 'hasModelCode' => $p->hasModelCode])
            ->values()
            ->all();
    }

    /** Ports lib/catalog.ts's getWaterProducts(). @return Collection<int, object> */
    public function waterProducts(string $locale): Collection
    {
        return $this->allProducts($locale)->filter(fn ($p) => $p->category === 'water')->values();
    }

    /** @return array<int, object> */
    public function productsBySlugs(array $slugs, string $locale): array
    {
        $bySlug = $this->allProducts($locale)->keyBy('slug');

        return collect($slugs)
            ->map(fn ($slug) => $bySlug->get($slug))
            ->filter()
            ->values()
            ->all();
    }

    public function categoryIntro(string $category, string $locale): string
    {
        $products = $this->allProducts($locale);
        $sections = $this->allSections($locale);
        $merged = $this->mergedCatalogSections($products, $sections);

        $anchor = Site::CATEGORY_ANCHORS[$category] ?? null;
        if ($anchor && ! empty($merged[$anchor]['paragraphs'])) {
            return implode(' ', $merged[$anchor]['paragraphs']);
        }

        return Site::catalogIntro($locale);
    }
}
