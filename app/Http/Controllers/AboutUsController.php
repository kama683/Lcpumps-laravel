<?php

namespace App\Http\Controllers;

use App\Support\Content;
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

    private const array PRIMARY_SLUGS = ['our-company', 'innovation', 'history', 'sustainability'];

    /**
     * Ports app/[locale]/about-us/[slug]/page.tsx's getExtraPageContent()
     * switch — only the 6 slugs NOT in PRIMARY_SLUGS. Unlike the batch 3
     * Applications/FAQ bug, this page calls Content::companyParagraphs()
     * WITH the real locale — no locale defect here.
     */
    private function extraPageContent(string $slug, string $locale): ?string
    {
        $company = Content::companyParagraphs($locale);

        return match ($slug) {
            'production' => $company[3] ?? '',
            'certifications' => $company[6] ?? '',
            'careers', 'media-relations', 'investor-relations', 'our-divisions' => Content::genericAboutMessage($locale),
            default => null,
        };
    }

    public function index(): View
    {
        $locale = app()->getLocale();
        $company = Content::companyParagraphs($locale);

        return view('pages.about-us.index', [
            // Was KNOWN_DEFECTS.md #2's <title>-only case: the source used a
            // static `export const metadata` ("О компании") instead of
            // generateMetadata(), so the <title> never localized even though
            // the page content itself always did. Fixed — reuses the
            // existing Nav.aboutUs key (same RU text, already translated to
            // kk/en/zh), no new translation copy needed.
            'metaTitle' => __('Nav.aboutUs'),
            'cert' => $company[6] ?? '',
            'service' => $company[5] ?? '',
            'clients' => $company[9] ?? '',
            'stats' => Content::aboutStats($locale),
            'awards' => Content::aboutAwards($locale),
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Nav.aboutUs')],
            ],
        ]);
    }

    public function show(Request $request): View
    {
        $slug = $this->routeParam($request, 'slug');
        $locale = app()->getLocale();

        $isPrimary = in_array($slug, self::PRIMARY_SLUGS, true);
        $sub = $isPrimary
            ? collect(Content::aboutSubpages($locale))->firstWhere('slug', $slug)
            : null;
        $extraContent = $isPrimary ? null : $this->extraPageContent($slug, $locale);

        if (! $sub && $extraContent === null) {
            abort(404);
        }

        $title = $isPrimary
            ? __('AboutUs.subnav.'.$slug)
            : __('AboutSubPage.extraPages.'.$slug);
        $paragraphs = $sub['paragraphs'] ?? [$extraContent];

        return view('pages.about-us.show', [
            'metaTitle' => $title,
            'title' => $title,
            'paragraphs' => $paragraphs,
            'isPrimary' => $isPrimary,
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Nav.aboutUs'), 'route' => 'about-us.index'],
                ['label' => $title],
            ],
        ]);
    }
}
