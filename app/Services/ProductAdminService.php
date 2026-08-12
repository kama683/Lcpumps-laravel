<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ported from app/admin/(protected)/products/actions.ts +
 * lib/repositories/products.ts. Validation order/messages match the source
 * exactly. Unlike the source, sort_order (a Laravel-only addition — the
 * source has no explicit catalog ordering column, see the migration
 * comment) needs an explicit value on create: a new product starts at the
 * end of its section (MAX(sort_order) WHERE section_id = X) + 1, so newly
 * added products don't jump to the front of the grid. Falls back to
 * category (the public catalog's other grouping key, see
 * CatalogService::mergedCatalogSections) when no section is assigned.
 */
class ProductAdminService
{
    public const array LOCALES = ['ru', 'en', 'kk', 'zh'];

    /** @return array{errors: array<string,string>}|array{} */
    public function create(array $input): array
    {
        $slug = trim((string) ($input['slug'] ?? ''));

        if ($slug === '') {
            return ['errors' => ['slug' => 'Укажите slug товара.']];
        }
        if (! preg_match('/^[a-z0-9-]+$/', $slug)) {
            return ['errors' => ['slug' => 'Slug может содержать только латинские буквы, цифры и дефис.']];
        }

        $ruName = trim((string) ($input['translations']['ru']['name'] ?? ''));
        if ($ruName === '') {
            return ['errors' => ['name_ru' => 'Укажите название товара (RU).']];
        }

        if (Product::query()->whereKey($slug)->exists()) {
            return ['errors' => ['slug' => 'Товар с таким slug уже существует.']];
        }

        DB::transaction(function () use ($slug, $input) {
            $sectionId = $input['section_id'] !== '' ? $input['section_id'] : null;
            $category = $input['category'];

            $sortOrder = 1 + (int) ($sectionId
                ? Product::query()->where('section_id', $sectionId)->max('sort_order')
                : Product::query()->where('category', $category)->max('sort_order'));

            $product = Product::create([
                'slug' => $slug,
                'code' => trim((string) $input['code']),
                'category' => $category,
                'section_id' => $sectionId,
                'image' => $input['image'] !== '' ? $input['image'] : null,
                'stock_quantity' => max(0, (int) $input['stock_quantity']),
                'price' => max(0, (int) $input['price']),
                'sort_order' => $sortOrder,
            ]);

            $this->saveTranslations($product, $input['translations']);
        });

        return [];
    }

    /** @return array{errors: array<string,string>}|array{} */
    public function update(string $slug, array $input): array
    {
        $ruName = trim((string) ($input['translations']['ru']['name'] ?? ''));
        if ($ruName === '') {
            return ['errors' => ['name_ru' => 'Укажите название товара (RU).']];
        }

        $product = Product::query()->findOrFail($slug);

        DB::transaction(function () use ($product, $input) {
            $product->update([
                'code' => trim((string) $input['code']),
                'category' => $input['category'],
                'section_id' => $input['section_id'] !== '' ? $input['section_id'] : null,
                'image' => $input['image'] !== '' ? $input['image'] : null,
                'stock_quantity' => max(0, (int) $input['stock_quantity']),
                'price' => max(0, (int) $input['price']),
            ]);

            $this->saveTranslations($product, $input['translations']);
        });

        return [];
    }

    public function delete(string $slug): void
    {
        // FK is cascadeOnDelete (product_translations) — see migration.
        Product::query()->whereKey($slug)->delete();
    }

    /** RU name only, enough for the admin list/search. @return Collection<int, Product> */
    public function listForAdmin(): Collection
    {
        return Product::query()
            ->join('product_translations', function ($join) {
                $join->on('product_translations.product_slug', '=', 'products.slug')
                    ->where('product_translations.locale', '=', 'ru');
            })
            ->orderBy('product_translations.name')
            ->select([
                'products.slug', 'products.code', 'products.category', 'products.section_id',
                'products.image', 'products.stock_quantity', 'products.price',
                'product_translations.name as ru_name',
            ])
            ->get();
    }

    /** @return array{slug:string,code:string,category:string,section_id:?string,image:?string,stock_quantity:int,price:int,translations:array<string,array{name:string,description:array,specs:array,applications:array}>}|null */
    public function getForAdmin(string $slug): ?array
    {
        $product = Product::query()->with('translations')->find($slug);
        if (! $product) {
            return null;
        }

        $byLocale = $product->translations->keyBy('locale');
        $ru = $byLocale->get('ru');

        $translations = [];
        foreach (self::LOCALES as $locale) {
            $t = $byLocale->get($locale) ?? $ru;
            $translations[$locale] = [
                'name' => $t->name ?? '',
                'description' => $t->description ?? [],
                'specs' => $t->specs ?? [],
                'applications' => $t->applications ?? [],
            ];
        }

        return [
            'slug' => $product->slug,
            'code' => $product->code,
            'category' => $product->category,
            'section_id' => $product->section_id,
            'image' => $product->image,
            'stock_quantity' => $product->stock_quantity,
            'price' => $product->price,
            'translations' => $translations,
        ];
    }

    /** RU titles, for the admin section <select> — the admin works in Russian regardless of which locale a product's other fields are being edited in. @return Collection<int, object{id:string,title:string}> */
    public function sectionOptions(): Collection
    {
        return Section::query()
            ->join('section_translations', function ($join) {
                $join->on('section_translations.section_id', '=', 'sections.id')
                    ->where('section_translations.locale', '=', 'ru');
            })
            ->orderBy('section_translations.title')
            ->select(['sections.id', 'section_translations.title'])
            ->get();
    }

    /** @param  array<string, array{name?:string,description?:array,specs?:array,applications?:array}>  $translations */
    private function saveTranslations(Product $product, array $translations): void
    {
        foreach (self::LOCALES as $locale) {
            $t = $translations[$locale] ?? [];

            ProductTranslation::query()->updateOrCreate(
                ['product_slug' => $product->slug, 'locale' => $locale],
                [
                    'name' => trim((string) ($t['name'] ?? '')),
                    'description' => array_values($t['description'] ?? []),
                    'specs' => array_values($t['specs'] ?? []),
                    'applications' => array_values($t['applications'] ?? []),
                ]
            );
        }
    }
}
