<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Section;
use App\Models\SectionTranslation;
use Illuminate\Database\Seeder;

/**
 * Ports scripts/migrate-catalog-to-db.ts from the Next.js source project:
 * RU text comes straight from catalog.json; en/kk/zh use the overrides in
 * catalog-i18n.ts when present, falling back to the RU text otherwise.
 * database/seeders/data/catalog-merged.json is a one-time export of both
 * source files (catalog.json + catalog-i18n.ts), produced by a throwaway
 * tsx script run against the Next.js project — see Stage 3 commit message.
 */
class CatalogSeeder extends Seeder
{
    private const array OTHER_LOCALES = ['en', 'kk', 'zh'];

    public function run(): void
    {
        $path = database_path('seeders/data/catalog-merged.json');
        $data = json_decode(file_get_contents($path), associative: true, flags: JSON_THROW_ON_ERROR);

        $unresolvedSections = [];
        $translationGaps = [];

        $this->seedSections($data);
        $this->seedProducts($data, $unresolvedSections, $translationGaps);

        $this->command?->info(sprintf(
            'Seeded %d sections and %d products.',
            count($data['catalog']['sections']),
            count($data['catalog']['products'])
        ));

        if ($unresolvedSections !== []) {
            $this->command?->warn(sprintf(
                '%d product(s) had a section that could not be resolved: %s',
                count($unresolvedSections),
                implode(', ', $unresolvedSections)
            ));
        }

        if ($translationGaps !== []) {
            $this->command?->warn(sprintf(
                '%d product/locale translation(s) fell back to RU text (no catalog-i18n override).',
                count($translationGaps)
            ));
        }
    }

    private function seedSections(array $data): void
    {
        foreach ($data['catalog']['sections'] as $section) {
            Section::updateOrCreate(
                ['id' => $section['id']],
                ['project_list' => $section['project_list'] ?? []]
            );

            SectionTranslation::updateOrCreate(
                ['section_id' => $section['id'], 'locale' => 'ru'],
                [
                    'title' => $section['title'],
                    'paragraphs' => $section['paragraphs'] ?? [],
                    'bullets' => $section['bullets'] ?? null,
                ]
            );

            foreach (self::OTHER_LOCALES as $locale) {
                $overlay = $data['sectionTextTranslations'][$section['id']][$locale] ?? null;

                // No dedicated section-title translation exists in the source
                // project either (lib/catalog.ts always uses the RU title) —
                // replicate that instead of inventing new behavior.
                SectionTranslation::updateOrCreate(
                    ['section_id' => $section['id'], 'locale' => $locale],
                    [
                        'title' => $section['title'],
                        'paragraphs' => $overlay['paragraphs'] ?? $section['paragraphs'] ?? [],
                        'bullets' => $overlay['bullets'] ?? $section['bullets'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedProducts(array $data, array &$unresolvedSections, array &$translationGaps): void
    {
        $sectionTitleToId = [];
        foreach ($data['catalog']['sections'] as $section) {
            $sectionTitleToId[$section['title']] = $section['id'];
        }

        foreach ($data['catalog']['products'] as $product) {
            $sectionId = null;
            if (!empty($product['section'])) {
                $sectionId = $sectionTitleToId[$product['section']] ?? null;
                if ($sectionId === null) {
                    $unresolvedSections[] = "{$product['slug']} (section: \"{$product['section']}\")";
                }
            }

            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'code' => $product['code'],
                    'category' => $product['category'],
                    'section_id' => $sectionId,
                    'image' => $product['image'] ?? null,
                ]
            );

            ProductTranslation::updateOrCreate(
                ['product_slug' => $product['slug'], 'locale' => 'ru'],
                [
                    'name' => $product['name'],
                    'description' => $product['description'] ?? [],
                    'specs' => $product['specs'] ?? [],
                    'applications' => $product['applications'] ?? [],
                ]
            );

            foreach (self::OTHER_LOCALES as $locale) {
                $t = $data['productTranslations'][$product['slug']][$locale] ?? null;
                if ($t === null) {
                    $translationGaps[] = "{$product['slug']} ({$locale})";
                }

                ProductTranslation::updateOrCreate(
                    ['product_slug' => $product['slug'], 'locale' => $locale],
                    [
                        'name' => $t['name'] ?? $product['name'],
                        'description' => $t['description'] ?? $product['description'] ?? [],
                        'specs' => $t['specs'] ?? $product['specs'] ?? [],
                        'applications' => $t['applications'] ?? $product['applications'] ?? [],
                    ]
                );
            }
        }
    }
}
