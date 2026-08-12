<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/applications/{page.tsx,water-wastewater/page.tsx}.
 *
 * Both source pages call getApplicationsCards()/getCompanyParagraphs() with
 * NO locale argument and use a static (Russian-only) `metadata` title
 * instead of generateMetadata() — a real bug on the live site, reproduced
 * byte-for-byte here rather than silently fixed. See KNOWN_DEFECTS.md #2
 * for the full audit, translation-availability findings, and the deferred
 * fix plan (needs client-approved copy for kk/en/zh before it can ship).
 */
class ApplicationsController extends Controller
{
    private const array CARD_ICONS = ['layers', 'building-2', 'factory', 'folder-kanban'];

    public function index(): View
    {
        $cards = Content::applicationsCards();
        $intro = Content::companyParagraphs()[2] ?? '';

        return view('pages.applications.index', [
            'metaTitle' => 'Применения',
            'cards' => $cards,
            'cardIcons' => self::CARD_ICONS,
            'intro' => $intro,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Applications.breadcrumbLabel')],
            ],
        ]);
    }

    public function waterWastewater(): View
    {
        $company = Content::companyParagraphs();
        $text = collect($company)->first(fn ($p) => preg_match('/сточн|водоснабж/iu', $p) === 1)
            ?? $company[2] ?? '';

        return view('pages.applications.water-wastewater', [
            'metaTitle' => 'Водоснабжение и сточные воды',
            'text' => $text,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Applications.breadcrumbLabel'), 'route' => 'applications.index'],
                ['label' => __('Applications.waterWastewaterLink')],
            ],
        ]);
    }
}
