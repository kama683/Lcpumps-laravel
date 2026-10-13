// Port of components/layout/Header.tsx's transparent-over-hero + mobile menu
// state. Class strings below are copied verbatim from the source's cn()
// branches (see Stage 5 dynamic-class report) so Tailwind's scanner still
// finds them — this file swaps between them wholesale rather than toggling
// individual utility fragments, to stay exactly in sync with the source.

const HEADER_TRANSPARENT = 'top-0 inset-x-0 z-50 border-b transition-colors duration-300 fixed bg-black/20 border-transparent';
const HEADER_SOLID_FIXED = 'top-0 inset-x-0 z-50 border-b transition-colors duration-300 fixed header-material bg-white/96 backdrop-blur-sm border-border-light';
const HEADER_SOLID_STICKY = 'top-0 inset-x-0 z-50 border-b transition-colors duration-300 sticky header-material bg-white/96 backdrop-blur-sm border-border-light';

const BRAND_TRANSPARENT = 'font-heading font-bold text-[28px] tablet:text-[32px] tracking-[-0.3px] leading-[1.15] transition-colors duration-300 text-white';
const BRAND_SOLID = 'font-heading font-bold text-[28px] tablet:text-[32px] tracking-[-0.3px] leading-[1.15] transition-colors duration-300 text-body';

const NAV_BASE = 'no-underline pb-0.5 transition-colors duration-300 ';
const NAV_TRANSPARENT_ACTIVE = NAV_BASE + 'text-white border-b-2 border-white';
const NAV_TRANSPARENT_INACTIVE = NAV_BASE + 'text-white/85 hover:text-white';
const NAV_SOLID_ACTIVE = NAV_BASE + 'text-primary border-b-2 border-primary';
const NAV_SOLID_INACTIVE = NAV_BASE + 'text-nav hover:text-primary';

const LANGUAGE_BASE = 'list-none [&::-webkit-details-marker]:hidden flex items-center gap-1.5 rounded-sm px-2 py-2.5 tablet:py-1 transition-colors duration-300 cursor-pointer active:opacity-60 ';
const LANGUAGE_TRANSPARENT = LANGUAGE_BASE + 'text-white hover:text-white/80 group-open:text-white';
const LANGUAGE_SOLID = LANGUAGE_BASE + 'text-nav hover:text-primary group-open:text-primary';

const MENU_BTN_TRANSPARENT = 'tablet:hidden ml-auto flex size-11 shrink-0 items-center justify-center rounded-md border transition-colors duration-300 border-white/40 text-white hover:border-white hover:text-white';
const MENU_BTN_SOLID = 'tablet:hidden ml-auto flex size-11 shrink-0 items-center justify-center rounded-md border transition-colors duration-300 border-border-mid text-heading hover:border-primary hover:text-primary';

const ICON_BASE = 'absolute inset-0 transition-[opacity,transform] duration-200 ease-(--ease-entrance) ';
const MENU_ICON_CLOSED = ICON_BASE + 'scale-100 rotate-0 opacity-100';
const MENU_ICON_OPEN = ICON_BASE + 'scale-75 rotate-45 opacity-0';
const CLOSE_ICON_CLOSED = ICON_BASE + 'scale-75 -rotate-45 opacity-0';
const CLOSE_ICON_OPEN = ICON_BASE + 'scale-100 rotate-0 opacity-100';

const PANEL_BASE = 'tablet:hidden grid overflow-hidden border-t border-border-light bg-white transition-[grid-template-rows] duration-300 ease-(--ease-entrance) ';
const PANEL_CLOSED = PANEL_BASE + 'grid-rows-[0fr]';
const PANEL_OPEN = PANEL_BASE + 'grid-rows-[1fr]';

function applyTransparentState(header, transparent, isHome) {
    header.className = transparent ? HEADER_TRANSPARENT : isHome ? HEADER_SOLID_FIXED : HEADER_SOLID_STICKY;

    const brand = header.querySelector('[data-header-brand]');
    if (brand) brand.className = transparent ? BRAND_TRANSPARENT : BRAND_SOLID;

    header.querySelectorAll('[data-header-nav-link]').forEach((link) => {
        const active = link.dataset.active === '1';
        link.className = transparent
            ? active
                ? NAV_TRANSPARENT_ACTIVE
                : NAV_TRANSPARENT_INACTIVE
            : active
              ? NAV_SOLID_ACTIVE
              : NAV_SOLID_INACTIVE;
    });

    const languageToggle = header.querySelector('[data-header-language-toggle]');
    if (languageToggle) languageToggle.className = transparent ? LANGUAGE_TRANSPARENT : LANGUAGE_SOLID;

    const menuToggle = header.querySelector('[data-header-menu-toggle]');
    if (menuToggle) menuToggle.className = transparent ? MENU_BTN_TRANSPARENT : MENU_BTN_SOLID;
}

export function initHeader() {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    const isHome = header.dataset.isHome === '1';
    let scrolled = false;
    let hovered = false;
    let open = false;

    const sync = () => applyTransparentState(header, isHome && !scrolled && !hovered, isHome);

    if (isHome) {
        header.addEventListener('mouseenter', () => {
            hovered = true;
            sync();
        });
        header.addEventListener('mouseleave', () => {
            hovered = false;
            sync();
        });
        const onScroll = () => {
            const next = window.scrollY > 40;
            if (next !== scrolled) {
                scrolled = next;
                sync();
            }
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    const toggle = header.querySelector('[data-header-menu-toggle]');
    const panel = header.querySelector('[data-header-mobile-panel]');
    const menuIcon = header.querySelector('[data-menu-icon]');
    const closeIcon = header.querySelector('[data-close-icon]');

    toggle?.addEventListener('click', () => {
        open = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute(
            'aria-label',
            open ? toggle.dataset.closeLabel : toggle.dataset.openLabel
        );
        panel.className = open ? PANEL_OPEN : PANEL_CLOSED;
        // menuIcon/closeIcon are <svg> elements — .className is a read-only
        // SVGAnimatedString there, not a plain string; assigning to it
        // throws (silently aborting the rest of this handler). setAttribute
        // works for both HTML and SVG elements.
        menuIcon.setAttribute('class', open ? MENU_ICON_OPEN : MENU_ICON_CLOSED);
        closeIcon.setAttribute('class', open ? CLOSE_ICON_OPEN : CLOSE_ICON_CLOSED);
    });
}
