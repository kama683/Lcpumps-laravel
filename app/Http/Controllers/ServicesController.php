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
