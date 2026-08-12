<?php

namespace App\Http\Controllers;

use App\Support\Content;
use Illuminate\Contracts\View\View;

/**
 * Ports app/[locale]/faq/page.tsx. Same locale-defect class as
 * KNOWN_DEFECTS.md #2: getFaqItems() called with no locale argument, so
 * all 4 questions + answers stay Russian on every locale.
 */
class FaqController extends Controller
{
    public function index(): View
    {
        return view('pages.faq.index', [
            'metaTitle' => __('PageMetadata.faq.title'),
            'items' => Content::faqItems(),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => 'FAQ'],
            ],
        ]);
    }
}
