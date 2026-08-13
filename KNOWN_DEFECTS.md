# Known defects carried over from the live site

These are real bugs on the live, currently-indexed Next.js site. Per project
policy, the Laravel port preserves them byte-for-byte rather than silently
"fixing" them mid-migration — visual/textual parity is what Stage 5-10
sign-off is measured against, and an unannounced fix here would show up as
an unexplained diff in every screenshot comparison. Each item below was
meant to be deferred to a **coordinated commit across both repos**
(`site/` and `laravel/`) after the client explicitly approves the fix,
since these are all visible/SEO-facing changes, not implementation
details.

> **✅ Process deviation resolved (2026-08-13).** Both defects below were
> initially fixed in `laravel/` only (commits `5b5e876`/`f7ba87a`), leaving
> `site/` untouched and breaking the "coordinated commit" policy this file
> was built around. That gap has since been closed: `site/` now carries
> the equivalent fixes (see each defect's status below), and both repos
> render identical, correctly localized content on all 4 locales —
> verified live via `curl` against both dev servers, not just read from
> source. `compare-screenshots.cjs` runs against these pages should once
> again show no diff between the two repos.
>
> While porting the fix into `site/`, a second bug was found in the
> **same commit** that introduced the original localization fix (i.e. not
> a defect of the live site — a regression caught before it shipped): the
> `/services` and `/applications/water-wastewater` call sites match a
> paragraph out of the (now locale-translated) content array using a
> Russian-only regex (`/филиалов/i`, `/сточн|водоснабж/i`). Once those
> call sites started passing the real locale through, the regex stopped
> matching for kk/en/zh and `/services` silently rendered an **empty**
> paragraph on those locales (`/applications/water-wastewater` fell back
> to a generic paragraph instead, less visibly wrong but still incorrect).
> Fixed in both repos by matching the regex against the RU-canonical array
> to find the paragraph's *index*, then reading that same index out of the
> locale-translated array — the same pattern `getFaqItems`/
> `getApplicationsCards` already used (`findCompanyIndex` in `site/`,
> `Content::findCompanyIndex` in `laravel/`, both promoted from private to
> exported/public for this). Verified via curl on all 4 locales in both
> repos after the fix; full Laravel test suite re-run clean (76/76).

---

## 1. Home hero H1: broken clamp() (found in Stage 5 batch 1)

`text-[clamp(30px,4vw,48px))]` has a stray closing paren, breaking the
arbitrary-value Tailwind utility. Affects the home page H1 on all locales.

**Status:** **Fixed in both repos.** `laravel/` (commit `f7ba87a`) —
`resources/views/pages/home.blade.php` now has the correct single-paren
class. Note: the initial fix commit changed the class but didn't rebuild
Tailwind assets, so the corrected class had no matching CSS rule and the
visual bug persisted after the "fix" landed — caught and corrected in a
follow-up (`public/build/` rebuilt, verified live via curl that the
compiled CSS now has exactly one rule for the corrected class, no
leftover rule for the old broken one). `site/` (2026-08-13) —
`app/[locale]/page.tsx:49` had the identical stray-paren typo, fixed the
same way; no phantom-CSS-rule issue here since this is plain JSX with no
comment echoing the literal class string. Verified live via curl on both
servers.

---

## 2. Applications/FAQ/Services content never localizes (found in Stage 5 batch 3)

Several pages call locale-aware content helpers **without passing the
current locale**, so the helpers fall back to their `locale: string = "ru"`
default regardless of which locale the page is actually rendering. Three of
these pages *also* use a static `export const metadata` object instead of
`generateMetadata()`, so the `<title>` tag is hardcoded Russian too, on top
of the body content.

Two independent bugs are involved — a page can have either, both, or
neither:

**A. Content not localized** — helper called with no `locale` arg:

| Page | Call site | What stays Russian |
|---|---|---|
| `/applications` | `app/[locale]/applications/page.tsx:21` `getApplicationsCards()` | All 4 application-card titles + body text |
| `/applications` | `app/[locale]/applications/page.tsx:22` `getCompanyParagraphs()[2]` | Intro paragraph |
| `/applications/water-wastewater` | `app/[locale]/applications/water-wastewater/page.tsx:15` `getCompanyParagraphs()` | Intro paragraph |
| `/services` | `app/[locale]/services/page.tsx:24` `getCompanyParagraphs().find(...)` | Service-network paragraph |
| `/faq` | `app/[locale]/faq/page.tsx:21` `getFaqItems()` | All 4 FAQ questions + answers |
| `/contact` | `app/[locale]/contact/page.tsx:23` `getFaqItems().slice(0,3)` | Mini-FAQ (3 of the 4 items above) |

**B. `<title>` not localized** — static `metadata` export instead of `generateMetadata()`:

| Page | Hardcoded title | Content itself localized correctly? |
|---|---|---|
| `/about-us` | "О компании" | Yes — `getCompanyParagraphs(locale)` is called correctly here, only the `<title>` is broken |
| `/applications` | "Применения" | No — see table A above, both bugs stack on this page |
| `/applications/water-wastewater` | "Водоснабжение и сточные воды" | No — see table A above, both bugs stack on this page |

(`app/admin/layout.tsx` also uses a static `metadata` title — **not a bug**,
the admin UI is intentionally Russian-only per the migration spec.)

Confirmed live via direct curl comparison, e.g. `/applications` vs
`/en/applications`: both return identical Russian `<h3>` card titles.

### Translation availability (checked against `data/catalog.json` and `messages/*.json`)

Not all affected strings are equally fixable — some already have real
per-locale translations sitting unused, others don't exist in any language
but Russian anywhere in the repo:

- **Already translated, just needs the locale arg wired through:**
  `catalogJson.company[locale]` (`lib/catalog-helpers.ts`'s
  `getCompanyParagraphs`) has 12 real, distinct paragraphs per locale for
  ru/kk/en/zh — not a Russian duplicate. This covers: the `/services`
  paragraph, the `/applications` and `/applications/water-wastewater` intro
  paragraphs, and the **body text** (not titles) of all 4 FAQ
  answers/Applications cards. Fixing the call sites to pass `locale` is
  sufficient for all of these.

- **No translation exists anywhere — would need to be ordered:**
  - The 4 FAQ **question** strings in `data/content.ts`'s `getFaqItems()`
    are Russian string literals hardcoded directly in the function body
    (e.g. `"Какие категории продукции выпускает LCPumps?"`), not pulled
    from `messages/*.json` or any locale table. No kk/en/zh version exists
    anywhere in the codebase.
  - The **answer** to the 1st FAQ question is *also* a hardcoded Russian
    literal (`data/content.ts` line ~210: `"Насосы, трубопроводная
    арматура, электрические шкафы управления и оборудование для
    водоснабжения."`) — unlike the other 3 answers, which pull from
    `company[locale]` (already translated). Easy to miss because it looks
    identical in shape to the other three `answer:` lines; confirmed by
    reading the function body, not assumed.
  - 3 of the 4 Applications **card titles** in `getApplicationsCards()`
    ("Строительство зданий", "Промышленные и горнодобывающие предприятия",
    "Проекты") are likewise Russian literals with no existing translation.
  - The 1st card's title ("Отрасли применения") is the one exception: it
    happens to duplicate `messages/*.json`'s `Applications.title` key,
    which **is** already fully translated in all 4 locales (e.g. en:
    "Industries of Application") — this one can be wired up programmatically
    without waiting on new translation copy.

**Bottom line for the client:** fixing the `<title>` tags and the paragraph/
answer *bodies* pulled from `company[locale]` is a code-only fix
(translations already exist). Fixing the FAQ question text, the 1st FAQ
answer, and 3 of the 4 Applications card titles additionally requires new
translated copy for kk/en/zh that doesn't exist yet in either repo — 8
distinct strings × 3 languages = **24 translations** to write (not 21 —
recount after finding the 1st-FAQ-answer case above). You've said you'll
write these yourselves rather than wait on the client, so this list is no
longer a blocker, just a to-do:
  1. Applications card title: "Строительство зданий"
  2. Applications card title: "Промышленные и горнодобывающие предприятия"
  3. Applications card title: "Проекты"
  4. FAQ question: "Какие категории продукции выпускает LCPumps?"
  5. FAQ answer (question 1 only): "Насосы, трубопроводная арматура, электрические шкафы управления и оборудование для водоснабжения."
  6. FAQ question: "В каких отраслях применяется продукция?"
  7. FAQ question: "Какие сертификаты имеет компания?"
  8. FAQ question: "Есть ли сервисная сеть?"

**Status:** **Fixed in both repos.**

`laravel/` (commits `5b5e876`/`f7ba87a`, 2026-08-13) — the 24 translations
above were written and added to `lang/{kk,en,zh}/{Applications,Faq}.php`
(new `cards.*`/`questions.*`/`answers.productCategories` keys);
`Content::applicationsCards()`/`faqItems()` now resolve titles/questions/
the first answer via `__()` against the passed-in `$locale` instead of
hardcoded Russian literals; `ApplicationsController`, `ServicesController`,
`FaqController`, `ContactController::index()` all now call these helpers
**with** the current locale. One page from table B was missed in the
original fix and completed in the same pass: `/about-us`'s `<title>` was
still hardcoded `'О компании'` in `AboutUsController::index()` — now uses
`__('Nav.aboutUs')` (an existing, already-translated key with the
identical RU text, no new translation copy needed).

The first pass at the kk translations for `Applications.php`/`Faq.php`
had grammatical errors (e.g. wrong case endings, an unrelated word
substituted for "pumps" in the FAQ's first answer) — caught by
cross-referencing against already-correct usage of the same words
elsewhere in the repo (`wastewaterPumpsLink`, `CatalogNav.php`) and
rewritten properly on 2026-08-13.

`site/` (2026-08-13) — the same 24 translations were added as new
`Record<AppLocale, ...>` maps (`FAQ_QUESTIONS`, `FAQ_FIRST_ANSWER`,
`APPLICATIONS_CARD_TITLES`) in `data/content.ts`; `getFaqItems()`/
`getApplicationsCards()` now take a `locale: AppLocale` parameter and
pull titles/questions/the first answer from these maps instead of
hardcoded Russian literals. All 6 call sites across `applications/`,
`applications/water-wastewater/`, `services/`, `faq/`, and `contact/`
pages now pass `useLocale()` through. `/about-us`'s static `metadata`
export was replaced with `generateMetadata()` reusing the existing
`Nav.aboutUs` translation key, matching the `laravel/` fix.

Verified live (not just read) in both repos: `curl`'d `/en/faq`,
`/kk/faq`, `/zh/faq`, `/en/applications` (both card text and `<title>`)
on both `127.0.0.1:8010` and `localhost:3000` — all correctly localized
and matching between the two repos. `laravel/` full test suite re-run
clean: 76/76. `site/` type-checked clean via `npx tsc --noEmit`.
