<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/applications/{page.tsx,water-wastewater/page.tsx}.
 *
 * Source pages had locale defect (getApplicationsCards()/getCompanyParagraphs()
 * called with NO locale argument) and hardcoded Russian `<title>`. Both are now
 * fixed: card titles and titles pull from lang-files with proper localization,
 * see KNOWN_DEFECTS.md #2 for the original audit and fix plan.
 */
class ApplicationsController extends Controller
{
    private const array CARD_ICONS = ['layers', 'building-2', 'factory', 'folder-kanban'];

    public function index(): View
    {
        $locale = app()->getLocale();
        $cards = Content::applicationsCards($locale);
        $intro = Content::companyParagraphs($locale)[2] ?? '';

        return view('pages.applications.index', [
            'metaTitle' => __('Applications.title'),
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
        $locale = app()->getLocale();
        $company = Content::companyParagraphs($locale);
        // Match against the RU-canonical array (Content::findCompanyIndex),
        // not $company directly — $company is already locale-translated,
        // so this Russian regex tested against it never matches for
        // kk/en/zh and silently falls through to the wrong paragraph.
        $textIdx = Content::findCompanyIndex('/сточн|водоснабж/iu');
        $text = ($textIdx >= 0 ? $company[$textIdx] : $company[2]) ?? '';

        return view('pages.applications.water-wastewater', [
            'metaTitle' => __('Applications.waterWastewaterLink'),
            'text' => $text,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Applications.breadcrumbLabel'), 'route' => 'applications.index'],
                ['label' => __('Applications.waterWastewaterLink')],
            ],
        ]);
    }
}
