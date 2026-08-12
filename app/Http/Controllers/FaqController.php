<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/faq/page.tsx. Source had locale defect
 * (getFaqItems() called with no locale argument) — now fixed, passes
 * current locale. See KNOWN_DEFECTS.md #2.
 */
class FaqController extends Controller
{
    public function index(): View
    {
        $locale = app()->getLocale();

        return view('pages.faq.index', [
            'metaTitle' => __('PageMetadata.faq.title'),
            'items' => Content::faqItems($locale),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => 'FAQ'],
            ],
        ]);
    }
}
