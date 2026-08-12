<?php

namespace App\Support;

/**
 * Ports the locale-aware parts of data/content.ts + lib/catalog-helpers.ts's
 * getCompanyParagraphs. resources/data/company.json is a one-time export of
 * catalog.json's `company` key (12 paragraphs per locale, ru/kk/en/zh — same
 * throwaway-script export approach as ProjectsRepository's projects.json).
 *
 * Per KNOWN_DEFECTS.md #2: several source call sites invoke
 * getCompanyParagraphs()/getApplicationsCards() with NO locale argument,
 * which silently falls back to the "ru" default — a real bug on the live
 * site that this port preserves byte-for-byte. companyParagraphs() below
 * mirrors that default (locale = 'ru') so call sites can reproduce the bug
 * simply by omitting the argument, same as the source.
 */
class Content
{
    private static ?array $company = null;

    private static function company(): array
    {
        return self::$company ??= json_decode(
            file_get_contents(resource_path('data/company.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    /** @return array<int, string> */
    public static function companyParagraphs(string $locale = 'ru'): array
    {
        return self::company()[$locale] ?? self::company()['ru'];
    }

    /** Finds a paragraph index in the canonical Russian source, so keyword
     * matching stays correct regardless of which locale's text is displayed. */
    private static function findCompanyIndex(string $pattern): int
    {
        foreach (self::companyParagraphs('ru') as $i => $paragraph) {
            if (preg_match($pattern, $paragraph) === 1) {
                return $i;
            }
        }

        return -1;
    }

    /** Plural version of findCompanyIndex — returns every matching index, not just the first. */
    private static function findCompanyIndices(string $pattern): array
    {
        $indices = [];
        foreach (self::companyParagraphs('ru') as $i => $paragraph) {
            if (preg_match($pattern, $paragraph) === 1) {
                $indices[] = $i;
            }
        }

        return $indices;
    }

    /**
     * Ports data/content.ts's getApplicationsCards(). Card titles now pull
     * from lang-files instead of hardcoded Russian strings, fixing KNOWN_DEFECTS.md #2.
     *
     * @return array<int, array{title: string, text: string}>
     */
    public static function applicationsCards(string $locale = 'ru'): array
    {
        $company = self::companyParagraphs($locale);
        $buildingIdx = self::findCompanyIndex('/строительств/iu');
        $industrialIdx = self::findCompanyIndex('/промышленн/iu');

        return [
            ['title' => __('Applications.cards.industriesOfApplication', locale: $locale), 'text' => $company[2] ?? ''],
            ['title' => __('Applications.cards.buildingConstruction', locale: $locale), 'text' => ($buildingIdx >= 0 ? $company[$buildingIdx] : $company[2]) ?? ''],
            ['title' => __('Applications.cards.industrialAndMining', locale: $locale), 'text' => ($industrialIdx >= 0 ? $company[$industrialIdx] : $company[2]) ?? ''],
            ['title' => __('Applications.cards.projects', locale: $locale), 'text' => $company[7] ?? ''],
        ];
    }

    /**
     * Ports data/content.ts's getFaqItems(). Question and first-answer text now pull
     * from lang-files instead of hardcoded Russian strings, fixing KNOWN_DEFECTS.md #2.
     * Only the answer paragraphs (except question 1) still pull from the locale-aware
     * company paragraphs. Filters out any item whose answer resolved empty, same as the source.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    public static function faqItems(string $locale = 'ru'): array
    {
        $company = self::companyParagraphs($locale);
        $certIdx = self::findCompanyIndex('/ISO|сертификац/iu');
        $serviceIdx = self::findCompanyIndex('/филиалов/iu');

        $items = [
            ['question' => __('Faq.questions.productCategories', locale: $locale), 'answer' => __('Faq.answers.productCategories', locale: $locale)],
            ['question' => __('Faq.questions.industries', locale: $locale), 'answer' => $company[2] ?? ''],
            ['question' => __('Faq.questions.certifications', locale: $locale), 'answer' => $certIdx >= 0 ? ($company[$certIdx] ?? '') : ''],
            ['question' => __('Faq.questions.serviceNetwork', locale: $locale), 'answer' => $serviceIdx >= 0 ? ($company[$serviceIdx] ?? '') : ''],
        ];

        return array_values(array_filter($items, fn ($item) => $item['answer'] !== ''));
    }

    /**
     * Ports data/content.ts's getAboutSubpages() — the 4 "primary" about-us
     * subpages (as opposed to the 6 "extra" ones in
     * AboutUsController::EXTRA_PAGE_CONTENT, which aren't in this list).
     *
     * @return array<int, array{slug: string, paragraphs: array<int, string>}>
     */
    public static function aboutSubpages(string $locale): array
    {
        $company = self::companyParagraphs($locale);
        $innovationIdx = array_slice(self::findCompanyIndices('/инновац|патент|технолог/iu'), 0, 3);
        $sustainabilityIdx = array_slice(self::findCompanyIndices('/окружающ|водн/iu'), 0, 3);

        return [
            ['slug' => 'our-company', 'paragraphs' => array_slice($company, 0, 4)],
            ['slug' => 'innovation', 'paragraphs' => array_map(fn ($i) => $company[$i], $innovationIdx)],
            ['slug' => 'history', 'paragraphs' => array_slice($company, 0, 2)],
            ['slug' => 'sustainability', 'paragraphs' => array_map(fn ($i) => $company[$i], $sustainabilityIdx)],
        ];
    }

    private const array GENERIC_ABOUT_MESSAGE = [
        'ru' => 'Информация о LCPumps доступна в разделе О компании.',
        'kk' => 'LCPumps туралы ақпаратты «Компания туралы» бөлімінен алуға болады.',
        'en' => 'Information about LCPumps is available in the About Us section.',
        'zh' => '有关LCPumps的信息，请查看"关于我们"板块。',
    ];

    public static function genericAboutMessage(string $locale): string
    {
        return self::GENERIC_ABOUT_MESSAGE[$locale] ?? self::GENERIC_ABOUT_MESSAGE['ru'];
    }

    /** @var array<string, array<int, array{value: string, label: string}>> */
    private const array ABOUT_STATS = [
        'ru' => [
            ['value' => '5', 'label' => 'промышленных парков'],
            ['value' => '550 000 м²', 'label' => 'производственных площадей'],
            ['value' => '2000+', 'label' => 'комплектов оборудования'],
            ['value' => '30+', 'label' => 'филиалов по Китаю'],
        ],
        'kk' => [
            ['value' => '5', 'label' => 'индустриялық парк'],
            ['value' => '550 000 м²', 'label' => 'өндірістік алаң'],
            ['value' => '2000+', 'label' => 'жабдық жинағы'],
            ['value' => '30+', 'label' => 'Қытай бойынша филиал'],
        ],
        'en' => [
            ['value' => '5', 'label' => 'industrial parks'],
            ['value' => '550,000 m²', 'label' => 'of production space'],
            ['value' => '2,000+', 'label' => 'sets of equipment'],
            ['value' => '30+', 'label' => 'branches across China'],
        ],
        'zh' => [
            ['value' => '5', 'label' => '工业园区'],
            ['value' => '550,000 m²', 'label' => '生产面积'],
            ['value' => '2,000+', 'label' => '台（套）设备'],
            ['value' => '30+', 'label' => '中国分支机构'],
        ],
    ];

    /** @return array<int, array{value: string, label: string}> */
    public static function aboutStats(string $locale): array
    {
        return self::ABOUT_STATS[$locale] ?? self::ABOUT_STATS['ru'];
    }

    /** @var array<string, array<int, string>> */
    private const array ABOUT_AWARDS = [
        'ru' => [
            'Инновационное предприятие',
            'Известная торговая марка',
            'Известная торговая марка Шанхая',
            'Известный бренд Китая',
            'Первое национальное предприятие по производству энергосберегающих насосов',
            'Шанхайское высокотехнологичное предприятие',
            '50 лучших частных производственных предприятий Шанхая',
            '100 лучших промышленных предприятий Шанхая',
            'Десять лучших национальных брендов водной отрасли Китая',
        ],
        'kk' => [
            'Инновациялық кәсіпорын',
            'Танымал сауда белгісі',
            'Шанхайдың танымал сауда белгісі',
            'Қытайдың танымал бренді',
            'Энергия үнемдейтін сорғы өндіру бойынша бірінші ұлттық кәсіпорын',
            'Шанхайдың жоғары технологиялық кәсіпорны',
            'Шанхайдың үздік 50 жеке өндірістік кәсіпорны',
            'Шанхайдың үздік 100 өнеркәсіптік кәсіпорны',
            'Қытайдың су саласындағы үздік он ұлттық бренді',
        ],
        'en' => [
            'Innovative Enterprise',
            'Well-Known Trademark',
            'Well-Known Trademark of Shanghai',
            'Well-Known Brand of China',
            'First National Enterprise for Energy-Saving Pump Manufacturing',
            'Shanghai High-Tech Enterprise',
            'Top 50 Private Manufacturing Enterprises of Shanghai',
            'Top 100 Industrial Enterprises of Shanghai',
            "Top Ten National Brands of China's Water Industry",
        ],
        'zh' => [
            '创新型企业',
            '知名商标',
            '上海市著名商标',
            '中国知名品牌',
            '首批国家级节能水泵生产企业',
            '上海市高新技术企业',
            '上海市50强民营制造企业',
            '上海市100强工业企业',
            '中国水行业十大民族品牌',
        ],
    ];

    /** @return array<int, string> */
    public static function aboutAwards(string $locale): array
    {
        return self::ABOUT_AWARDS[$locale] ?? self::ABOUT_AWARDS['ru'];
    }

    /** Locale-independent, ported from data/content.ts's aboutCertifications. */
    public const array ABOUT_CERTIFICATIONS = ['ISO 09001', 'ISO 14001', 'OHSAS 18001', 'CCC', 'CQC', 'CE', 'MA', 'SMA'];

    /**
     * Locale-independent, ported from data/content.ts's aboutClients.
     *
     * @var array<int, array{name: string, logo: string}>
     */
    public const array ABOUT_CLIENTS = [
        ['name' => 'General Motors', 'logo' => '/assets/clients/general-motors.svg'],
        ['name' => 'Bayer', 'logo' => '/assets/clients/bayer.svg'],
        ['name' => 'Siemens', 'logo' => '/assets/clients/siemens.svg'],
        ['name' => 'Volkswagen', 'logo' => '/assets/clients/volkswagen.svg'],
        ['name' => 'Coca-Cola', 'logo' => '/assets/clients/coca-cola.svg'],
    ];
}
