<?php

namespace Tests\Feature;

use App\Services\SeoMetadataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://www.lcpumps.kz']);
        app()->setLocale('ru');

        // RefreshDatabase migrates the full schema (sections, products,
        // product_translations, ...) so pages that touch more than just the
        // products table (e.g. category pages joining sections) render
        // correctly under test, not just the sitemap. Only the row data
        // below is fixture-specific.
        $count = 68; // matches the exported catalog size used elsewhere
        $inserts = [];
        $translations = [];
        for ($i = 1; $i <= $count; $i++) {
            $slug = 'product-' . $i;
            $inserts[] = [
                'slug' => $slug,
                'code' => sprintf('P%03d', $i),
                'category' => 'pumps',
                'sort_order' => $i,
                'stock_quantity' => 10,
                'price' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $translations[] = [
                'product_slug' => $slug,
                'locale' => 'ru',
                'name' => "Товар {$i}",
                'description' => json_encode([]),
                'specs' => json_encode([]),
                'applications' => json_encode([]),
            ];
        }

        DB::table('products')->insert($inserts);
        DB::table('product_translations')->insert($translations);
    }

    public function test_robots_txt_matches_live_site_rules(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /api', $content);
        $this->assertStringContainsString('Sitemap: https://www.lcpumps.kz/sitemap.xml', $content);
    }

    public function test_sitemap_xml_is_generated_with_localized_alternates(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $content = $response->getContent();
        // Persist the response for offline debugging in CI if needed.
        @file_put_contents(base_path('sitemap-debug.xml'), $content);

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('https://www.lcpumps.kz/', $content);
        $this->assertStringContainsString('xhtml:link', $content);
        $this->assertStringContainsString('hreflang="ru"', $content);
        $this->assertStringContainsString('hreflang="kk"', $content);
        $this->assertStringContainsString('hreflang="en"', $content);
        $this->assertStringContainsString('hreflang="zh"', $content);
        // Count <url> nodes robustly (XML may include attributes on the tag).
        preg_match_all('/<url(\s|>)/', $content, $matches);
        $this->assertGreaterThan(50, count($matches[0]));
    }

    public function test_canonical_urls_are_derived_from_config_not_host_header(): void
    {
        $service = app(SeoMetadataService::class);

        $url = $service->absoluteUrlForRoute('home', [], 'ru');
        $this->assertSame('https://www.lcpumps.kz/', $url);
        $this->assertStringNotContainsString('evil.example', $url);

        $this->withServerVariables(['HTTP_HOST' => 'evil.example']);
        $this->assertSame('https://www.lcpumps.kz/', $service->absoluteUrlForRoute('home', [], 'ru'));
    }

    public function test_public_routes_have_hreflang_links_for_all_locales_and_x_default(): void
    {
        $service = app(SeoMetadataService::class);
        $locales = ['ru', 'kk', 'en', 'zh'];

        foreach ($service->publicRoutes() as $route) {
            $links = $service->hreflangUrls($route['name'], $route['params']);

            $this->assertArrayHasKey('ru', $links);
            $this->assertArrayHasKey('kk', $links);
            $this->assertArrayHasKey('en', $links);
            $this->assertArrayHasKey('zh', $links);
            $this->assertArrayHasKey('x-default', $links);
            $this->assertSame($links['ru'], $links['x-default']);

            foreach ($locales as $locale) {
                $this->assertStringStartsWith('https://www.lcpumps.kz', $links[$locale]);
                $this->assertSame($links[$locale], $service->absoluteUrlForRoute($route['name'], $route['params'], $locale));
            }
        }
    }

    /**
     * Not just "does the service compute the right URLs" (the test above) —
     * this hits actual rendered pages through the HTTP kernel and reads the
     * <link rel="alternate"> tags out of the response body, on both ends of
     * the relationship: /page must link to /kk/page, AND /kk/page must link
     * back to the exact same /page URL. That reciprocity is what search
     * engines actually require and what a service-level unit test can't
     * catch (e.g. a layout that never got the hreflang loop wired in at
     * all would still pass the test above).
     */
    public function test_rendered_public_pages_include_reciprocal_hreflang_links(): void
    {
        $service = app(SeoMetadataService::class);
        $locales = ['ru', 'kk', 'en', 'zh'];

        // A representative sample, not the full 33-route sitemap list: one
        // static page, one page with a slug param, and one category page —
        // enough to exercise every route "shape" without this test's runtime
        // scaling with the size of the catalog.
        $sample = [
            ['name' => 'home', 'params' => []],
            ['name' => 'applications.index', 'params' => []],
            ['name' => 'applications.water-wastewater', 'params' => []],
            ['name' => 'services.index', 'params' => []],
            ['name' => 'faq.index', 'params' => []],
            ['name' => 'about-us.show', 'params' => ['slug' => 'our-company']],
            ['name' => 'products.pumps', 'params' => []],
            ['name' => 'products.show', 'params' => ['slug' => 'product-1']],
        ];

        foreach ($sample as $route) {
            $links = $service->hreflangUrls($route['name'], $route['params']);
            $ruUrl = $links['ru'];
            $ruPath = parse_url($ruUrl, PHP_URL_PATH);

            $ruResponse = $this->get($ruPath);
            $ruResponse->assertOk();
            $ruHtml = $ruResponse->getContent();

            foreach ($locales as $locale) {
                $this->assertStringContainsString(
                    sprintf('<link rel="alternate" hreflang="%s" href="%s">', $locale, $links[$locale]),
                    $ruHtml,
                    "Rendered {$ruPath} is missing its hreflang={$locale} link"
                );
            }
            $this->assertStringContainsString(
                sprintf('<link rel="alternate" hreflang="x-default" href="%s">', $links['x-default']),
                $ruHtml
            );

            // Reciprocity: fetch the kk variant and confirm ITS OWN hreflang
            // set points back to the exact ru URL we started from, not just
            // that both independently agree with the service.
            $kkPath = parse_url($links['kk'], PHP_URL_PATH);
            $kkResponse = $this->get($kkPath);
            $kkResponse->assertOk();
            $this->assertStringContainsString(
                sprintf('<link rel="alternate" hreflang="ru" href="%s">', $ruUrl),
                $kkResponse->getContent(),
                "Rendered {$kkPath} does not link back to {$ruUrl} — hreflang is not reciprocal"
            );
        }
    }

    public function test_admin_pages_have_no_hreflang_links(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $this->assertStringNotContainsString('rel="alternate"', $response->getContent());
    }
}
