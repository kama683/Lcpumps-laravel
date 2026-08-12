<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/services/page.tsx. Same locale-defect class as
 * KNOWN_DEFECTS.md #2: getCompanyParagraphs() called with no locale
 * argument, so the service paragraph stays Russian on every locale — the
 * page's <title> is unaffected (real generateMetadata(), not static).
 */
class ServicesController extends Controller
{
    public function index(): View
    {
        $company = Content::companyParagraphs();
        $servicePara = collect($company)->first(fn ($p) => preg_match('/филиалов/iu', $p) === 1) ?? '';

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
