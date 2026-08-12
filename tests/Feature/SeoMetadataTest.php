<?php

namespace Tests\Feature;

use App\Services\SeoMetadataService;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://www.lcpumps.kz']);
        app()->setLocale('ru');

        // Ensure a minimal products table and translations exist for the
        // in-memory test DB so the sitemap can be generated deterministically.
        // Creating lightweight schema here avoids depending on the full
        // migration stack during fast CI runs.
        \Illuminate\Support\Facades\Schema::dropIfExists('product_translations');
        \Illuminate\Support\Facades\Schema::dropIfExists('products');

        \Illuminate\Support\Facades\Schema::create('products', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('slug')->primary();
            $table->string('code')->nullable();
            $table->string('category')->default('pumps');
            $table->string('section_id')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('product_translations', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('product_slug');
            $table->string('locale');
            $table->string('name')->nullable();
            $table->json('description')->nullable();
            $table->json('specs')->nullable();
            $table->json('applications')->nullable();
            $table->unique(['product_slug', 'locale']);
        });

        // Seed a reasonable number of products so sitemap size assertions pass.
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

        \Illuminate\Support\Facades\DB::table('products')->insert($inserts);
        \Illuminate\Support\Facades\DB::table('product_translations')->insert($translations);
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
}
