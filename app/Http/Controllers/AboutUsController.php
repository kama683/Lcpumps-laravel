<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    /**
     * Closed set of valid /about-us/{slug} values, ported from
     * PRIMARY_SUBPAGE_SLUGS (data/content.ts's getAboutSubpages) + the
     * EXTRA_PAGE_SLUGS list hardcoded in app/[locale]/about-us/[slug]/page.tsx.
     * Deliberately larger than sitemap.ts's ABOUT_SUBPAGE_SLUGS (only 4) —
     * that asymmetry exists in the source project too and Stage 9 preserves
     * it rather than "fixing" it, per exact-parity requirements.
     */
    public const array VALID_SLUGS = [
        'our-company', 'innovation', 'history', 'sustainability',
        'production', 'certifications', 'careers', 'media-relations',
        'investor-relations', 'our-divisions',
    ];

    public function index(): View
    {
        return $this->stub('about-us.index');
    }

    public function show(Request $request): View
    {
        return $this->stub('about-us.show', ['slug' => $this->routeParam($request, 'slug')]);
    }
}
