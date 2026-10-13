<?php

namespace App\Services;

use App\Support\Routing\LocalizedRoutes;

class SeoMetadataService
{
    public const array LOCALES = ['ru', 'kk', 'en', 'zh'];

    public function __construct(private readonly CatalogService $catalog) {}

    public function baseUrl(): string
    {
        $url = rtrim((string) config('app.url', 'https://www.lcpumps.kz'), '/');

        return $url !== '' ? $url : 'https://www.lcpumps.kz';
    }

    public function sitemapEntries(): array
    {
        $entries = [];

        foreach ($this->publicSitemapRoutes() as $route) {
            $entries[] = [
                'loc' => $this->absoluteUrlForRoute($route['name'], $route['params'], 'ru'),
                'alternates' => $this->alternateUrls($route['name'], $route['params']),
            ];
        }

        return $entries;
    }

    public function publicSitemapRoutes(): array
    {
        $routes = [
            ['name' => 'home', 'params' => []],
            ['name' => 'about-us.index', 'params' => []],
            ['name' => 'applications.index', 'params' => []],
            ['name' => 'applications.water-wastewater', 'params' => []],
            ['name' => 'contact.index', 'params' => []],
            ['name' => 'faq.index', 'params' => []],
            ['name' => 'news.index', 'params' => []],
            ['name' => 'privacy-policy', 'params' => []],
            ['name' => 'products.index', 'params' => []],
            ['name' => 'products.control', 'params' => []],
            ['name' => 'products.pumps', 'params' => []],
            ['name' => 'products.valves', 'params' => []],
            ['name' => 'products.water', 'params' => []],
            ['name' => 'projects.index', 'params' => []],
            ['name' => 'services.index', 'params' => []],
            ['name' => 'solutions.index', 'params' => []],
        ];

        foreach (['our-company', 'innovation', 'history', 'sustainability'] as $slug) {
            $routes[] = ['name' => 'about-us.show', 'params' => ['slug' => $slug]];
        }

        $this->catalog->allProducts('ru')->pluck('slug')->each(function (string $slug) use (&$routes): void {
            $routes[] = ['name' => 'products.show', 'params' => ['slug' => $slug]];
        });

        foreach (ProjectsRepository::forLocale('ru')['featured'] as $project) {
            $routes[] = ['name' => 'projects.show', 'params' => ['slug' => $project['slug']]];
        }

        return $routes;
    }

    public function publicRoutes(): array
    {
        return $this->publicSitemapRoutes();
    }

    public function alternateUrls(string $routeName, array $params = []): array
    {
        $urls = [];
        foreach (self::LOCALES as $locale) {
            $urls[$locale] = $this->absoluteUrlForRoute($routeName, $params, $locale);
        }

        return $urls;
    }

    public function hreflangUrls(string $routeName, array $params = []): array
    {
        $urls = $this->alternateUrls($routeName, $params);
        $urls['x-default'] = $this->absoluteUrlForRoute($routeName, $params, 'ru');

        return $urls;
    }

    /**
     * hreflang links for whatever public route is currently being rendered,
     * for use straight from the shared layout (components/layouts/app.blade.php)
     * without every controller having to pass routeName/params down manually.
     *
     * Returns [] outside of a routed request (e.g. no route matched) rather
     * than throwing, so a stray call from a non-page context degrades to "no
     * hreflang links" instead of a 500.
     */
    public function hreflangForCurrentRequest(): array
    {
        $route = request()->route();
        $name = $route?->getName();

        if ($name === null) {
            return [];
        }

        if (str_starts_with($name, LocalizedRoutes::LOCALIZED_NAME_PREFIX)) {
            $name = substr($name, strlen(LocalizedRoutes::LOCALIZED_NAME_PREFIX));
        }

        // Only URI placeholders belong in alternate URLs. Route::view also
        // stores its internal "view" and "status" defaults as parameters;
        // passing those to route() would leak them into hreflang query strings.
        $params = array_intersect_key(
            $route->parameters(),
            array_flip($route->parameterNames()),
        );
        unset($params['locale']);

        return $this->hreflangUrls($name, $params);
    }

    public function absoluteUrlForRoute(string $routeName, array $params = [], string $locale = 'ru'): string
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            $url = route($routeName, $params, true);

            // Ensure the home route ends with a trailing slash to match the
            // canonical format used across the project (https://www.lcpumps.kz/).
            if ($routeName === 'home' && $url !== '' && ! str_ends_with($url, '/')) {
                $url = $url.'/';
            }

            return $url;
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    public function canonicalUrl(string $routeName, array $params = []): string
    {
        return $this->absoluteUrlForRoute($routeName, $params, 'ru');
    }

    public function titleForRoute(string $routeName, array $params = [], string $locale = 'ru'): ?string
    {
        return $this->resolveTitle($routeName, $params, $locale);
    }

    public function descriptionForRoute(string $routeName, array $params = [], string $locale = 'ru'): ?string
    {
        return $this->resolveDescription($routeName, $params, $locale);
    }

    public function metadataForRoute(string $routeName, array $params = [], string $locale = 'ru'): array
    {
        return [
            'title' => $this->resolveTitle($routeName, $params, $locale),
            'description' => $this->resolveDescription($routeName, $params, $locale),
            'canonical' => $this->canonicalUrl($routeName, $params),
            'hreflang' => $this->hreflangUrls($routeName, $params),
        ];
    }

    private function resolveTitle(string $routeName, array $params = [], string $locale = 'ru'): ?string
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            return match ($routeName) {
                'home' => __('PageMetadata.home.title'),
                'about-us.index' => 'О компании',
                'about-us.show' => $this->resolveAboutSubpageTitle($params['slug'] ?? ''),
                'applications.index' => 'Применения',
                'applications.water-wastewater' => 'Водоснабжение и сточные воды',
                'contact.index' => __('PageMetadata.contact.title'),
                'faq.index' => __('PageMetadata.faq.title'),
                'news.index' => __('PageMetadata.news.title'),
                'privacy-policy' => __('PrivacyPolicy.meta.title'),
                'products.index' => __('CategoryPage.allPumpsTitle'),
                'products.pumps' => __('CategoryPage.pumpsTitle'),
                'products.valves' => __('CategoryPage.valvesTitle'),
                'products.control' => __('CategoryPage.controlTitle'),
                'products.water' => __('CategoryPage.waterTitle'),
                'products.show' => $this->resolveProductTitle((string) ($params['slug'] ?? '')),
                'projects.index' => __('PageMetadata.projects.title'),
                'projects.show' => $this->resolveProjectTitle((string) ($params['slug'] ?? '')),
                'services.index' => __('PageMetadata.services.title'),
                'solutions.index' => __('PageMetadata.solutions.title'),
                default => null,
            };
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    private function resolveDescription(string $routeName, array $params = [], string $locale = 'ru'): ?string
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);

        try {
            return match ($routeName) {
                'home' => __('PageMetadata.home.description'),
                'contact.index' => __('PageMetadata.contact.description'),
                'faq.index' => __('PageMetadata.faq.description'),
                'news.index' => __('PageMetadata.news.description'),
                'privacy-policy' => __('PrivacyPolicy.meta.description'),
                'projects.index' => __('PageMetadata.projects.description'),
                'services.index' => __('PageMetadata.services.description'),
                'solutions.index' => __('PageMetadata.solutions.description'),
                default => null,
            };
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    private function resolveAboutSubpageTitle(string $slug): string
    {
        $primary = ['our-company', 'innovation', 'history', 'sustainability'];
        $extra = ['production', 'certifications', 'careers', 'media-relations', 'investor-relations', 'our-divisions'];

        if (in_array($slug, $primary, true)) {
            return __('AboutUs.subnav.'.$slug, [], app()->getLocale());
        }

        if (in_array($slug, $extra, true)) {
            return __('AboutSubPage.extraPages.'.$slug, [], app()->getLocale());
        }

        return __('Nav.aboutUs', [], app()->getLocale());
    }

    private function resolveProductTitle(string $slug): ?string
    {
        $product = $this->catalog->allProducts(app()->getLocale())->firstWhere('slug', $slug);
        if (! $product) {
            return __('CategoryPage.pumpsTitle');
        }

        $name = $product->name;
        if ($product->hasModelCode) {
            return trim((string) $product->code).' — '.$name;
        }

        return $name;
    }

    private function resolveProjectTitle(string $slug): string
    {
        $project = ProjectsRepository::bySlug($slug, app()->getLocale());

        return $project['name'] ?? __('Nav.projects');
    }
}
