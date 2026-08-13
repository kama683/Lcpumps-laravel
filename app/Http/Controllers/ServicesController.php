<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/services/page.tsx. Source had locale defect
 * (getCompanyParagraphs() called with no locale argument) — now fixed,
 * passes current locale. See KNOWN_DEFECTS.md #2.
 */
class ServicesController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();
        $company = Content::companyParagraphs($locale);
        // Match the keyword against the RU-canonical array, not $company
        // directly — $company is already locale-translated, so a Russian
        // regex tested against it never matches for kk/en/zh and silently
        // returns an empty paragraph.
        $serviceIdx = Content::findCompanyIndex('/филиалов/iu');
        $servicePara = $serviceIdx >= 0 ? ($company[$serviceIdx] ?? '') : '';

        return view('pages.services.index', [
            'metaTitle' => __('PageMetadata.services.title'),
            'servicePara' => $servicePara,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Services.label')],
            ],
        ]);
    }
}
