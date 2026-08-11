<?php

namespace App\Support;

/**
 * Ported from lib/site.ts. Nav item `label`/`title` strings are RU fallback
 * text only — display text comes from lang/*.php (Nav.*, CatalogNav.*) at
 * render time, same split as the source.
 */
class Site
{
    public const string BRAND = 'LCPumps';

    public const string COMPANY_NAME = 'LCPumps';

    public const string LOGO_PATH = '/assets/logo/newlogo(v5).png';

    public const string CATALOG_INTRO = 'LCPumps — ведущий производитель насосов в Китае. Продукция широко используется в таких национальных опорных областях, как муниципальное управление, водное хозяйство, строительство, противопожарная защита, электроэнергетика, охрана окружающей среды, нефтяная, химическая промышленность, горнодобывающая промышленность и медицина. LCPumps предоставляет высококачественные решения в области центробежных насосов для предприятий и проектов по всему миру благодаря своему мощному техническому накоплению и возможностям НИОКР.';

    /** @var array<int, array{href: string, labelKey: string}> */
    public const array MAIN_NAV = [
        ['href' => '/', 'labelKey' => 'home'],
        ['href' => '/about-us', 'labelKey' => 'aboutUs'],
        ['href' => '/products', 'labelKey' => 'catalog'],
        ['href' => '/projects', 'labelKey' => 'projects'],
        ['href' => '/contact', 'labelKey' => 'contacts'],
    ];

    /** @var array<int, array{id: string, title: string, category: string, page: string}> */
    public const array CATALOG_NAV = [
        ['id' => 'fire-pump', 'title' => 'Пожарное насосное оборудование по стандарту FM / UL', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'single-suction-centrifugal', 'title' => 'Общепромышленное применение и строительство', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'wastewater-submersible', 'title' => 'Насосы, смесители и оборудование для сточной воды', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'double-suction-split-case', 'title' => 'Промышленное применение и сельское хозяйство', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'petrochemical', 'title' => 'Нефтяная и химическая промышленность, API610', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'control-panel', 'title' => 'Электрические шкафы управления', 'category' => 'pumps', 'page' => '/products'],
        ['id' => 'water-supply-complete', 'title' => 'Модульные решения', 'category' => 'water', 'page' => '/products/water'],
        ['id' => 'valves', 'title' => 'Трубопроводная арматура', 'category' => 'valves', 'page' => '/products/valves'],
    ];

    /** Values are CatalogNav ids, not display text. */
    public const array CATEGORY_ANCHORS = [
        'water' => 'water-supply-complete',
        'valves' => 'valves',
    ];

    public const array HOME_FEATURED_PRODUCTS = [
        'xbd-slw2', 'xbd-sls2', 'xbd-dv', 'xbd-dw', 'xbd-gdl', 'xbd-d',
    ];

    public const array RESERVED_PRODUCT_SLUGS = ['pumps', 'valves', 'control', 'water'];

    /** Home hero background slideshow — crossfades through these in order. */
    public const array HERO_SLIDES = [
        ['src' => '/assets/hero/hero-1.png', 'alt' => ''],
        ['src' => '/assets/hero/hero-2.png', 'alt' => ''],
        ['src' => '/assets/hero/hero-3.png', 'alt' => ''],
    ];

    private const array HOME_STATS = [
        'ru' => [
            ['value' => '5', 'label' => 'промышленных парков'],
            ['value' => '550 000', 'label' => 'м² производственных площадей'],
            ['value' => '2000+', 'label' => 'комплектов оборудования'],
            ['value' => '30+', 'label' => 'филиалов'],
            ['value' => '1800+', 'label' => 'специалистов продаж и сервиса'],
        ],
        'kk' => [
            ['value' => '5', 'label' => 'индустриялық парк'],
            ['value' => '550 000', 'label' => 'м² өндірістік алаң'],
            ['value' => '2000+', 'label' => 'жабдық жинағы'],
            ['value' => '30+', 'label' => 'филиал'],
            ['value' => '1800+', 'label' => 'сату және сервис маманы'],
        ],
        'en' => [
            ['value' => '5', 'label' => 'industrial parks'],
            ['value' => '550,000', 'label' => 'm² of production space'],
            ['value' => '2,000+', 'label' => 'sets of equipment'],
            ['value' => '30+', 'label' => 'branches'],
            ['value' => '1,800+', 'label' => 'sales and service specialists'],
        ],
        'zh' => [
            ['value' => '5', 'label' => '工业园区'],
            ['value' => '550,000', 'label' => 'm² 生产面积'],
            ['value' => '2,000+', 'label' => '台（套）设备'],
            ['value' => '30+', 'label' => '分支机构'],
            ['value' => '1,800+', 'label' => '销售与服务专家'],
        ],
    ];

    /** @return array<int, array{value: string, label: string}> */
    public static function homeStats(string $locale): array
    {
        return self::HOME_STATS[$locale] ?? self::HOME_STATS['ru'];
    }

    private const array CATALOG_INTRO_TRANSLATIONS = [
        'en' => 'LCPumps is a leading pump manufacturer in China. Its products are widely used in key national sectors such as municipal management, water resources, construction, fire protection, power engineering, environmental protection, and the oil, chemical, and mining industries, as well as in medicine. Thanks to its strong technical foundation and R&D capabilities, LCPumps provides high-quality centrifugal pump solutions for enterprises and projects around the world.',
        'kk' => 'LCPumps — Қытайдағы жетекші сорғы өндірушісі. Өнім муниципалдық басқару, су шаруашылығы, құрылыс, өртке қарсы қорғаныш, электр энергетикасы, қоршаған ортаны қорғау, мұнай, химия өнеркәсібі, тау-кен өнеркәсібі және медицина сияқты ұлттық тірек салаларында кеңінен қолданылады. LCPumps қуатты техникалық жинақталған тәжірибесі мен ҒЗТКЖ мүмкіндіктерінің арқасында әлемнің түкпір-түкпіріндегі кәсіпорындар мен жобалар үшін жоғары сапалы центрифугалық сорғы шешімдерін ұсынады.',
        'zh' => 'LCPumps是中国领先的水泵制造商。其产品广泛应用于市政管理、水利、建筑、消防、电力、环保以及石油、化工、采矿等国家重点支柱领域,并涉及医疗领域。凭借深厚的技术积累和研发能力,LCPumps为全球企业和项目提供高品质的离心泵解决方案。',
    ];

    public static function catalogIntro(string $locale): string
    {
        return self::CATALOG_INTRO_TRANSLATIONS[$locale] ?? self::CATALOG_INTRO;
    }
}
