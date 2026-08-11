<?php

namespace App\Support;

/** Ported verbatim from lib/product-images.ts. */
class ProductImages
{
    private const string SUBMERSIBLE_SEWAGE_BASE = '/assets/catalog/submersible-sewage';

    private const string END_SUCTION_BASE = '/assets/catalog/end-suction-centrifugal';

    private const string DOUBLE_SUCTION_BASE = '/assets/catalog/double-suction-split-case';

    private const string LONG_SHAFT_BASE = '/assets/catalog/vertical-long-shaft';

    private const string AXIAL_MIXED_BASE = '/assets/catalog/axial-mixed-flow';

    private const string OIL_CHEMICAL_BASE = '/assets/catalog/oil-chemical-pump';

    private const string CONTROL_PANEL_BASE = '/assets/catalog/electric-control-panel';

    private const string FIRE_PUMP_BASE = '/assets/catalog/fire-extinguishing-pump';

    private const string MODULAR_SOLUTIONS_BASE = '/assets/catalog/modular-solutions';

    private const string CATALOG_ROOT_BASE = '/assets/catalog';

    private const string FALLBACK_PRODUCT_IMAGE = '/assets/pump.png';

    /** Product slug => catalog image path. */
    private static function bySlug(): array
    {
        return [
            'lpt' => self::LONG_SHAFT_BASE.'/LP-T-_1675843651_WNo_800d450.jpg',
            'qz' => self::AXIAL_MIXED_BASE.'/QZ-QH_1675844509_WNo_800d450.jpg',
            'yw' => self::SUBMERSIBLE_SEWAGE_BASE.'/1675924789021_1675925578_WNo_252d408.png',
            'zhlbq' => self::AXIAL_MIXED_BASE.'/Z-H-LB_1677374615_WNo_800d450.jpg',
            'wqx' => self::SUBMERSIBLE_SEWAGE_BASE.'/WQX_1675841251_WNo_800d450.jpg',
            'wq-ii' => self::SUBMERSIBLE_SEWAGE_BASE.'/WQ-II-_1675840944_WNo_800d450.jpg',
            'wl' => self::SUBMERSIBLE_SEWAGE_BASE.'/WL_1675840886_WNo_800d450.jpg',
            'wqc' => self::SUBMERSIBLE_SEWAGE_BASE.'/WQC-2-_1675840434_WNo_800d450.jpg',
            'wq' => self::SUBMERSIBLE_SEWAGE_BASE.'/WQ_1675840180_WNo_800d450.jpg',
            'slnc' => self::END_SUCTION_BASE.'/SLNC_1677545320_WNo_800d450.jpg',
            'sls' => self::END_SUCTION_BASE.'/SLS_1677545468_WNo_800d450.jpg',
            'slw' => self::END_SUCTION_BASE.'/SLW-2-_1677544915_WNo_800d450.jpg',
            'gdl' => self::END_SUCTION_BASE.'/GDL_1675848229_WNo_800d450.jpg',
            'dg' => self::END_SUCTION_BASE.'/DG_1675848201_WNo_800d450.jpg',
            'sld' => self::END_SUCTION_BASE.'/SLD-2-_1675848148_WNo_800d450.jpg',
            'md' => self::END_SUCTION_BASE.'/MD_1675848056_WNo_800d450.jpg',
            'slg' => self::END_SUCTION_BASE.'/SLG-2-_1675847980_WNo_800d450.jpg',
            'slown' => self::DOUBLE_SUCTION_BASE.'/SLOWN.jpeg',
            'slow' => self::DOUBLE_SUCTION_BASE.'/SLO-SLOW.jpg',
            'slow-2stage' => self::DOUBLE_SUCTION_BASE.'/SLOW.jpg',
            'slow-multistage' => self::CATALOG_ROOT_BASE.'/SLOW.png',
            'slda' => self::OIL_CHEMICAL_BASE.'/SLDA-BB1_1677374780_WNo_800d450.jpg',
            'ly' => self::OIL_CHEMICAL_BASE.'/LY-VS1.png',
            'ayg' => self::OIL_CHEMICAL_BASE.'/AYG-OH3.png',
            'xl' => self::OIL_CHEMICAL_BASE.'/XL-_1677456564_WNo_800d450.jpg',
            'slzae' => self::OIL_CHEMICAL_BASE.'/SLZAE_1677374970_WNo_800d450.jpg',
            'slzaf' => self::OIL_CHEMICAL_BASE.'/SLZA-5-_1675845630_WNo_800d450.jpg',
            'slza' => self::OIL_CHEMICAL_BASE.'/SLZA-2-_1675845529_WNo_800d450.jpg',
            'slcz' => self::OIL_CHEMICAL_BASE.'/SLCZ_1677375071_WNo_800d450.jpg',
            'slmc' => self::OIL_CHEMICAL_BASE.'/SLMC-BB4_1677375226_WNo_800d450.jpg',
            'ay' => self::OIL_CHEMICAL_BASE.'/AY.png',
            'ay-multistage' => self::OIL_CHEMICAL_BASE.'/AY.png',
            'sldc' => self::OIL_CHEMICAL_BASE.'/SLDC.png',
            'sldtd' => self::OIL_CHEMICAL_BASE.'/'.rawurlencode('SLDT(D).png'),
            'slfz' => self::OIL_CHEMICAL_BASE.'/SLFZ.png',
            'slpp' => self::OIL_CHEMICAL_BASE.'/SLPP.png',
            'model-626113' => self::OIL_CHEMICAL_BASE.'/'.rawurlencode('SLZA E F.png'),
            'tmc' => self::OIL_CHEMICAL_BASE.'/'.rawurlencode('TMC TTMC.png'),
            'lec' => self::CONTROL_PANEL_BASE.'/LEC.png',
            'lbp' => self::CONTROL_PANEL_BASE.'/LBP.png',
            'hv-control-cabinet' => self::CONTROL_PANEL_BASE.'/'.rawurlencode('ВЫОКОВОЛЬТНЫЙ ЭЛЕКТРИЧКСКИЙ ШКАФ УПРАВЛЕНИЯ.png'),
            'lfb' => self::CONTROL_PANEL_BASE.'/LFB.png',
            'lv-distribution' => self::CONTROL_PANEL_BASE.'/'.rawurlencode('РАСПРЕДЕЛИТЕЛЬНОЕ УСТРОЙСТВО НИЗКОГО НАПРЯЖЕНИЯ.png'),
            'xbd-d' => self::FIRE_PUMP_BASE.'/image_1677374308_WNo_800d450.jpg',
            'xbd-dv' => self::FIRE_PUMP_BASE.'/XBD-DV-2-_1677374063_WNo_800d450.jpg',
            'xbd-dw' => self::FIRE_PUMP_BASE.'/XBD-DW-2-_1677374116_WNo_800d450.jpg',
            'xbd-w' => self::FIRE_PUMP_BASE.'/SBD-SLW_1677461586_WNo_800d450.jpg',
            'xbd-slw2' => self::FIRE_PUMP_BASE.'/XBD-SLW_1675842059_WNo_800d450.jpg',
            'xbd-sls2' => self::FIRE_PUMP_BASE.'/XBD-SLS_1675841926_WNo_800d450.jpg',
            'xbd-slow' => self::FIRE_PUMP_BASE.'/XBD-SLOW_1675841622_WNo_800d450.jpg',
            'xbd-gdl' => self::FIRE_PUMP_BASE.'/XBD-GDL_1675841533_WNo_800d450.jpg',
            'xbc' => self::FIRE_PUMP_BASE.'/XBC.png',
            'xbd-c-lpt' => self::FIRE_PUMP_BASE.'/'.rawurlencode('XBD C-LP(T).png'),
            'fire-stable-water' => self::FIRE_PUMP_BASE.'/'.rawurlencode('ПРОТИВОПОЖАРНОЕ ОБОРУДОВАНИЕ ДЛЯ СТАБИЛЬНОГО ВОДОСНАБЖЕНИЯ.png'),
            'smart-pump-station' => self::MODULAR_SOLUTIONS_BASE.'/LCZH_1677805732617_WNo_400d225.jpg',
            'smart-pump-room' => self::MODULAR_SOLUTIONS_BASE.'/LCZF.png',
            'zwlv' => self::MODULAR_SOLUTIONS_BASE.'/ZWL-V-_1675845725_WNo_800d450.jpg',
            'water-vfd-full' => self::MODULAR_SOLUTIONS_BASE.'/'.rawurlencode('ОБОРУДОВАНИЕ ДЛЯ ВОДОСНАБЖЕНИЯ С ПОЛНОСТЬЮ РЕГУЛИРУЕМОЙ ЧАСТОТОЙ.png'),
            'water-vfd-direct' => self::MODULAR_SOLUTIONS_BASE.'/'.rawurlencode('ОБОРУДОВАНИЕ ВОДОСНАБЖЕНИЯ С МИКРОПРОЦЕССОРНЫМ ПРЕОБРАЗОВАНИЕМ ЧАСТОТЫ ДЛЯ ПРЯМОГО ПОДКЛЮЧЕНИЯ.png'),
            'zwl' => self::MODULAR_SOLUTIONS_BASE.'/ZWL.png',
            'water-multi-tank-vfd' => self::MODULAR_SOLUTIONS_BASE.'/'.rawurlencode('МНОГОРЕЗЕРВУАРНОЕ ОБОРУДОВАНИЕ ДЛЯ ПОДАЧИ ВОДЫ С ЧАСТОТНЫМ ПРЕОБРАЗОВАТЕЛЕМ.png'),
            'ktl' => self::CATALOG_ROOT_BASE.'/'.rawurlencode('KTL, KTW.png'),
            'is' => self::CATALOG_ROOT_BASE.'/IS.png',
            's' => self::CATALOG_ROOT_BASE.'/S.png',
            'sll' => self::CATALOG_ROOT_BASE.'/SLL.png',
            'qgls' => self::CATALOG_ROOT_BASE.'/QGLS.png',
            'qjb' => self::CATALOG_ROOT_BASE.'/QJB.png',
            'zw' => self::CATALOG_ROOT_BASE.'/ZW.png',
        ];
    }

    /** Dedicated category hero slideshows — each panel crossfades through its own set. */
    public static function categoryPanelHeroImages(): array
    {
        return [
            'wastewater-submersible' => [
                self::SUBMERSIBLE_SEWAGE_BASE.'/category-hero-1.png',
                self::SUBMERSIBLE_SEWAGE_BASE.'/category-hero-2.png',
            ],
            'single-suction-centrifugal' => [
                self::END_SUCTION_BASE.'/category-hero-1.png',
                self::END_SUCTION_BASE.'/category-hero-2.png',
                self::END_SUCTION_BASE.'/category-hero-3.png',
                self::END_SUCTION_BASE.'/category-hero-4.png',
            ],
            'double-suction-split-case' => [
                self::DOUBLE_SUCTION_BASE.'/category-hero-1.png',
                self::DOUBLE_SUCTION_BASE.'/category-hero-2.png',
                self::DOUBLE_SUCTION_BASE.'/category-hero-3.png',
                self::DOUBLE_SUCTION_BASE.'/category-hero-4.png',
            ],
            'petrochemical' => [
                self::OIL_CHEMICAL_BASE.'/category-hero-1.png',
                self::OIL_CHEMICAL_BASE.'/category-hero-2.png',
            ],
            'control-panel' => [
                self::CONTROL_PANEL_BASE.'/category-hero-1.png',
                self::CONTROL_PANEL_BASE.'/category-hero-2.png',
                self::CONTROL_PANEL_BASE.'/category-hero-3.png',
            ],
            'fire-pump' => [
                self::FIRE_PUMP_BASE.'/category-hero-1.png',
                self::FIRE_PUMP_BASE.'/category-hero-2.png',
            ],
            'water-supply-complete' => [
                self::MODULAR_SOLUTIONS_BASE.'/category-hero-1.png',
                self::MODULAR_SOLUTIONS_BASE.'/category-hero-2.png',
            ],
        ];
    }

    public static function categoryPanelHeroAlt(string $panelId): string
    {
        foreach (Site::CATALOG_NAV as $item) {
            if ($item['id'] === $panelId) {
                return $item['title'];
            }
        }

        return 'Каталог';
    }

    /** @param array{slug: string, image?: string|null} $product */
    public static function productImageSrc(array $product): string
    {
        return $product['image']
            ?? self::bySlug()[$product['slug']]
            ?? self::FALLBACK_PRODUCT_IMAGE;
    }
}
