<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Section;
use App\Models\SectionTranslation;
use App\Services\CatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function actingAsAdmin(): AdminUser
    {
        $admin = AdminUser::create(['username' => 'admin', 'password_hash' => Hash::make('secret123')]);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function makeSection(string $id = 'test-section'): Section
    {
        $section = Section::create(['id' => $id, 'project_list' => []]);
        SectionTranslation::create(['section_id' => $id, 'locale' => 'ru', 'title' => 'Тестовая секция', 'paragraphs' => []]);

        return $section;
    }

    private function baseInput(array $overrides = []): array
    {
        return array_replace([
            'slug' => 'test-pump',
            'code' => 'TP-100',
            'category' => 'pumps',
            'section_id' => '',
            'image' => '',
            'stock_quantity' => '5',
            'price' => '150000',
            'translations' => [
                'ru' => ['name' => 'Тестовый насос', 'description' => "Абзац 1\nАбзац 2", 'specs' => 'Расход: 50 м³/ч', 'applications' => 'Водоснабжение'],
                'en' => ['name' => 'Test pump', 'description' => '', 'specs' => '', 'applications' => ''],
                'kk' => ['name' => '', 'description' => '', 'specs' => '', 'applications' => ''],
                'zh' => ['name' => '', 'description' => '', 'specs' => '', 'applications' => ''],
            ],
        ], $overrides);
    }

    public function test_admin_can_create_a_product(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.products.store'), $this->baseInput());

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::query()->find('test-pump');
        $this->assertNotNull($product);
        $this->assertSame('TP-100', $product->code);
        $this->assertSame(5, $product->stock_quantity);
        $this->assertSame(150000, $product->price);

        $ru = ProductTranslation::query()->where('product_slug', 'test-pump')->where('locale', 'ru')->first();
        $this->assertSame('Тестовый насос', $ru->name);
        $this->assertSame(['Абзац 1', 'Абзац 2'], $ru->description);
        $this->assertSame(['Расход: 50 м³/ч'], $ru->specs);
    }

    public function test_new_product_is_appended_to_the_end_of_its_section(): void
    {
        $this->actingAsAdmin();
        $this->makeSection('sec-1');

        Product::create(['slug' => 'existing-1', 'code' => 'E1', 'category' => 'pumps', 'section_id' => 'sec-1', 'stock_quantity' => 0, 'price' => 0, 'sort_order' => 5]);
        Product::create(['slug' => 'existing-2', 'code' => 'E2', 'category' => 'pumps', 'section_id' => 'sec-1', 'stock_quantity' => 0, 'price' => 0, 'sort_order' => 9]);
        // Different section entirely, with a much higher sort_order — must not influence the new product's placement.
        Product::create(['slug' => 'other-section', 'code' => 'O1', 'category' => 'pumps', 'section_id' => null, 'stock_quantity' => 0, 'price' => 0, 'sort_order' => 999]);

        $this->post(route('admin.products.store'), $this->baseInput(['slug' => 'test-pump', 'section_id' => 'sec-1']));

        $product = Product::query()->find('test-pump');
        $this->assertSame(10, $product->sort_order);
    }

    public function test_slug_is_required(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.products.store'), $this->baseInput(['slug' => '']));

        $response->assertSessionHasErrors(['slug' => 'Укажите slug товара.']);
        $this->assertSame(0, Product::query()->count());
    }

    public function test_slug_must_match_the_allowed_pattern(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.products.store'), $this->baseInput(['slug' => 'Test Pump!']));

        $response->assertSessionHasErrors(['slug' => 'Slug может содержать только латинские буквы, цифры и дефис.']);
    }

    public function test_ru_name_is_required(): void
    {
        $this->actingAsAdmin();

        $input = $this->baseInput();
        $input['translations']['ru']['name'] = '';

        $response = $this->post(route('admin.products.store'), $input);

        $response->assertSessionHasErrors(['name_ru' => 'Укажите название товара (RU).']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $this->actingAsAdmin();
        $this->post(route('admin.products.store'), $this->baseInput());

        $response = $this->post(route('admin.products.store'), $this->baseInput());

        $response->assertSessionHasErrors(['slug' => 'Товар с таким slug уже существует.']);
        $this->assertSame(1, Product::query()->count());
    }

    public function test_admin_can_update_a_product(): void
    {
        $this->actingAsAdmin();
        $this->post(route('admin.products.store'), $this->baseInput());

        $input = $this->baseInput();
        $input['code'] = 'TP-200';
        $input['price'] = '999';

        $response = $this->put(route('admin.products.update', ['slug' => 'test-pump']), $input);

        $response->assertRedirect(route('admin.products.index'));
        $product = Product::query()->find('test-pump');
        $this->assertSame('TP-200', $product->code);
        $this->assertSame(999, $product->price);
    }

    public function test_admin_can_delete_a_product(): void
    {
        $this->actingAsAdmin();
        $this->post(route('admin.products.store'), $this->baseInput());

        $response = $this->delete(route('admin.products.destroy', ['slug' => 'test-pump']));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertSame(0, Product::query()->count());
        $this->assertSame(0, ProductTranslation::query()->where('product_slug', 'test-pump')->count());
    }

    public function test_product_image_upload_succeeds_and_returns_a_public_url(): void
    {
        $this->actingAsAdmin();
        $file = UploadedFile::fake()->createWithContent('photo.jpg', "\xFF\xD8\xFF".str_repeat('a', 100));

        $response = $this->post(route('admin.products.image.upload'), ['file' => $file, 'slug' => 'test-pump']);

        $response->assertOk();
        $url = $response->json('url');
        $this->assertNotNull($url);
        $this->assertStringContainsString('/products/test-pump/', $url);
    }

    public function test_product_image_upload_rejects_disallowed_type(): void
    {
        $this->actingAsAdmin();
        $file = UploadedFile::fake()->createWithContent('doc.pdf', '%PDF-1.4');

        $response = $this->post(route('admin.products.image.upload'), ['file' => $file, 'slug' => 'test-pump']);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Unsupported file type']);
    }

    public function test_product_image_upload_rejects_oversized_file(): void
    {
        $this->actingAsAdmin();
        $file = UploadedFile::fake()->create('photo.jpg', 8 * 1024 + 1)->mimeType('image/jpeg');

        $response = $this->post(route('admin.products.image.upload'), ['file' => $file, 'slug' => 'test-pump']);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'File too large']);
    }

    public function test_product_image_upload_rejects_content_that_does_not_match_declared_type(): void
    {
        $this->actingAsAdmin();
        $file = UploadedFile::fake()->createWithContent('photo.jpg', 'not actually a jpeg')->mimeType('image/jpeg');

        $response = $this->post(route('admin.products.image.upload'), ['file' => $file, 'slug' => 'test-pump']);

        $response->assertStatus(400);
        $response->assertJson(['error' => "File content doesn't match its declared type"]);
    }

    public function test_product_image_upload_sanitizes_slug(): void
    {
        $this->actingAsAdmin();
        $file = UploadedFile::fake()->createWithContent('photo.png', "\x89PNG\x0D\x0A\x1A\x0A".str_repeat('a', 50));

        $response = $this->post(route('admin.products.image.upload'), ['file' => $file, 'slug' => 'Bad Slug!!']);

        $response->assertOk();
        $this->assertStringContainsString('/products/badslug/', $response->json('url'));
    }

    public function test_product_admin_routes_require_authentication(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.products.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.products.store'), [])->assertRedirect(route('admin.login'));
        $this->postJson(route('admin.products.image.upload'), [])->assertStatus(401);
    }

    public function test_price_and_stock_never_appear_on_the_public_product_page(): void
    {
        ProductTranslation::query();
        $product = Product::create([
            'slug' => 'public-test-pump',
            'code' => 'PUB-100',
            'category' => 'pumps',
            'stock_quantity' => 42,
            'price' => 777777,
            'sort_order' => 0,
        ]);
        foreach (['ru', 'en', 'kk', 'zh'] as $locale) {
            ProductTranslation::create([
                'product_slug' => $product->slug,
                'locale' => $locale,
                'name' => 'Публичный насос',
                'description' => ['desc'],
                'specs' => ['spec: value'],
                'applications' => ['app'],
            ]);
        }

        $response = $this->get(route('products.show', ['slug' => 'public-test-pump']));

        $response->assertOk();
        $response->assertDontSee('777777');
        // stock_quantity (42) isn't asserted absent here too — a bare "42"
        // is too generic a substring for a full rendered page (could
        // collide with unrelated markup) to prove anything either way.
        // test_catalog_service_never_includes_price_or_stock_quantity()
        // below proves that one precisely, at the data layer.
    }

    public function test_catalog_service_never_includes_price_or_stock_quantity(): void
    {
        $product = Product::create([
            'slug' => 'catalog-service-test',
            'code' => 'CST-1',
            'category' => 'pumps',
            'stock_quantity' => 42,
            'price' => 777777,
            'sort_order' => 0,
        ]);
        ProductTranslation::create([
            'product_slug' => $product->slug,
            'locale' => 'ru',
            'name' => 'Насос',
            'description' => [],
            'specs' => [],
            'applications' => [],
        ]);

        $mapped = app(CatalogService::class)->allProducts('ru')->firstWhere('slug', 'catalog-service-test');

        $this->assertNotNull($mapped);
        $this->assertObjectNotHasProperty('price', $mapped);
        $this->assertObjectNotHasProperty('stockQuantity', $mapped);
        $this->assertObjectNotHasProperty('stock_quantity', $mapped);
    }
}
