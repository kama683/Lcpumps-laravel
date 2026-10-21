<?php

namespace Tests\Feature;

use App\Services\SeoMetadataService;
use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_all_privacy_policy_translation_files_have_the_same_complete_shape(): void
    {
        $flatten = function (array $values, string $prefix = '') use (&$flatten): array {
            $result = [];

            foreach ($values as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

                if (is_array($value)) {
                    $result += $flatten($value, $path);
                } else {
                    $result[$path] = $value;
                }
            }

            return $result;
        };

        $reference = $flatten(require lang_path('ru/PrivacyPolicy.php'));

        foreach (['kk', 'en', 'zh'] as $locale) {
            $translation = $flatten(require lang_path($locale.'/PrivacyPolicy.php'));

            $this->assertSame(array_keys($reference), array_keys($translation), "{$locale} translation key shape differs from ru");
            $this->assertNotContains('', array_values($translation), "{$locale} translation contains an empty value");
        }
    }

    public function test_privacy_policy_is_fully_localized_and_public(): void
    {
        $cases = [
            'ru' => [
                'path' => '/privacy-policy',
                'title' => 'Политика конфиденциальности | LC PUMPS',
                'heading' => 'Политика конфиденциальности',
                'lead' => 'Настоящая Политика описывает',
                'removed_update_line' => 'Последнее обновление: 1 сентября 2026 года',
                'contact' => 'Компания',
            ],
            'kk' => [
                'path' => '/kk/privacy-policy',
                'title' => 'Құпиялылық саясаты | LC PUMPS',
                'heading' => 'Құпиялылық саясаты',
                'lead' => 'Осы Саясат LC PUMPS компаниясының',
                'removed_update_line' => 'Соңғы жаңарту: 2026 жылғы 1 қыркүйек',
                'contact' => 'Компания',
            ],
            'en' => [
                'path' => '/en/privacy-policy',
                'title' => 'Privacy Policy | LC PUMPS',
                'heading' => 'Privacy Policy',
                'lead' => 'This Policy explains how LC PUMPS',
                'removed_update_line' => 'Last updated: 1 September 2026',
                'contact' => 'Company',
            ],
            'zh' => [
                'path' => '/zh/privacy-policy',
                'title' => '隐私政策 | LC PUMPS',
                'heading' => '隐私政策',
                'lead' => '本政策说明 LC PUMPS 如何处理和保护',
                'removed_update_line' => '最后更新：2026年9月1日',
                'contact' => '公司',
            ],
        ];

        foreach ($cases as $locale => $case) {
            $response = $this->get($case['path']);
            $html = $response->getContent();

            $response->assertOk();
            $response->assertSee('<html lang="'.$locale.'">', false);
            $response->assertSee('<title>'.$case['title'].'</title>', false);
            $response->assertSee($case['heading']);
            $response->assertSee($case['lead']);
            $response->assertDontSee($case['removed_update_line']);
            $response->assertSee($case['contact']);
            $response->assertSee('WhatsApp Business Platform');
            $response->assertSee('Meta');
            $response->assertSee('href="mailto:info@lcpumps.kz"', false);
            $response->assertSee('href="tel:+77717786877"', false);
            $response->assertSee('href="https://lcpumps.kz"', false);
            $response->assertDontSee('PrivacyPolicy.', false);

            $this->assertSame(8, preg_match_all('/id="privacy-section-[1-8]"/', $html));
            $this->assertSame(20, substr_count($html, '<li>'));

            if ($locale !== 'ru') {
                $this->assertStringNotContainsString('Официальный документ', $html);
                $this->assertStringNotContainsString('Последнее обновление', $html);
                $this->assertStringNotContainsString('Общие положения', $html);
            }
        }
    }

    public function test_privacy_policy_has_canonical_and_reciprocal_hreflang_links(): void
    {
        $seo = app(SeoMetadataService::class);
        $links = $seo->hreflangUrls('privacy-policy');

        foreach (['ru', 'kk', 'en', 'zh'] as $locale) {
            $path = parse_url($links[$locale], PHP_URL_PATH);
            $response = $this->get($path);

            $response->assertOk();
            $response->assertSee('<link rel="canonical" href="'.$links[$locale].'">', false);

            foreach (['ru', 'kk', 'en', 'zh', 'x-default'] as $hreflang) {
                $response->assertSee(
                    '<link rel="alternate" hreflang="'.$hreflang.'" href="'.$links[$hreflang].'">',
                    false,
                );
            }
        }
    }

    public function test_explicit_ru_prefix_redirects_to_the_unprefixed_canonical_policy(): void
    {
        $response = $this->get('/ru/privacy-policy');

        $response->assertRedirect('/privacy-policy');
        $response->assertStatus(307);
    }

    public function test_footer_links_to_the_current_locale_policy(): void
    {
        $seo = app(SeoMetadataService::class);

        foreach (['ru', 'kk', 'en', 'zh'] as $locale) {
            $url = $seo->absoluteUrlForRoute('privacy-policy', [], $locale);
            $response = $this->get(parse_url($url, PHP_URL_PATH));

            $response->assertOk();
            $response->assertSee('href="'.$url.'"', false);
        }
    }
}
