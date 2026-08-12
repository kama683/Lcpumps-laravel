// Port of components/catalog/{CatalogPanelContext,CatalogLayout,CategoryHeroImage}.tsx:
// URL-hash-driven category tab switching. All panel bodies are already
// rendered server-side (see resources/views/pages/products/pumps.blade.php);
// this only toggles visibility/fade and swaps the shared hero image, exactly
// like the source's client-only panel state.
const HERO_SLIDE_INTERVAL_MS = 3000;

const NAV_ACTIVE = 'block w-full text-left py-4 px-5 text-sm font-semibold border-b border-border-light leading-snug transition-[background,color,box-shadow] duration-150 active:opacity-60 bg-surface-alt text-primary shadow-[inset_3px_0_0_#f70620]';
const NAV_INACTIVE = 'block w-full text-left py-4 px-5 text-sm font-semibold border-b border-border-light leading-snug transition-[background,color,box-shadow] duration-150 active:opacity-60 text-muted hover:bg-surface hover:text-primary';

export function initCatalogPanel() {
    const root = document.querySelector('[data-catalog-panels]');
    if (!root) return;

    const panelIds = JSON.parse(root.dataset.panelIds || '[]');
    const defaultPanelId = root.dataset.defaultPanel;
    const defaultTitle = root.dataset.defaultTitle || '';
    const panelLabels = JSON.parse(document.getElementById('catalog-panel-labels')?.textContent || '{}');
    const heroData = JSON.parse(document.getElementById('catalog-hero-data')?.textContent || '{}');
    const heroContainer = document.querySelector('[data-category-hero]');
    const heroHeading = document.querySelector('[data-catalog-heading]');
    const mobileLabel = document.querySelector('[data-active-panel-label]');
    const categoryMenuToggle = document.querySelector('[data-category-menu-toggle]');

    let heroSlideTimer = null;

    function resolveActivePanel() {
        const raw = window.location.hash.replace('#', '');
        const normalized = raw.replace(/_/g, '-');
        if (panelIds.includes(normalized)) return normalized;
        if (panelIds.includes(raw)) return raw;
        return defaultPanelId;
    }

    function renderHero(panelId) {
        if (!heroContainer) return;
        const data = heroData[panelId];
        if (!data) return;
        if (heroSlideTimer) {
            clearInterval(heroSlideTimer);
            heroSlideTimer = null;
        }

        heroContainer.dataset.panelId = panelId;
        heroContainer.innerHTML = '';
        const images = data.images && data.images.length ? data.images : [data.fallbackSrc];
        images.forEach((src, i) => {
            const img = document.createElement('img');
            img.src = src;
            img.alt = data.alt;
            img.loading = 'eager';
            img.className = 'absolute inset-0 size-full object-center object-cover transition-opacity ease-in-out';
            img.style.opacity = i === 0 ? '1' : '0';
            img.style.transitionDuration = '700ms';
            heroContainer.appendChild(img);
        });

        if (images.length > 1) {
            const imgs = heroContainer.querySelectorAll('img');
            let index = 0;
            heroSlideTimer = setInterval(() => {
                index = (index + 1) % imgs.length;
                imgs.forEach((img, i) => {
                    img.style.opacity = i === index ? '1' : '0';
                });
            }, HERO_SLIDE_INTERVAL_MS);
        }
    }

    function activate(panelId) {
        document.querySelectorAll('[data-catalog-panel]').forEach((el) => {
            const match = el.dataset.catalogPanel === panelId;
            el.hidden = !match;
            el.classList.toggle('hidden', !match);
            el.classList.toggle('block', match);
            if (match) {
                el.classList.add('opacity-0');
                requestAnimationFrame(() => requestAnimationFrame(() => el.classList.remove('opacity-0')));
            }
        });

        document.querySelectorAll('[data-panel-nav-button]').forEach((btn) => {
            const active = btn.dataset.panelId === panelId;
            btn.className = active ? NAV_ACTIVE : NAV_INACTIVE;
        });

        // Mobile label always resolves via direct translation, same as the
        // source's tCatalogNav(activePanel) — panelLabels now covers every
        // CatalogNav id, not just this page's real panels, so this never
        // blanks out. The H1 only uses that translation when panelId is one
        // of this page's real panels; otherwise it falls back to the static
        // page title, matching CatalogPageShell's `activePanelData ? ... : title`.
        const label = panelLabels[panelId] || '';
        if (mobileLabel) mobileLabel.textContent = label;
        if (heroHeading) heroHeading.textContent = panelIds.includes(panelId) ? label : defaultTitle;

        renderHero(panelId);
    }

    window.addEventListener('hashchange', () => activate(resolveActivePanel()));

    document.querySelectorAll('[data-panel-nav-button]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.panelId;
            history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${id}`);
            activate(id);
            categoryMenuToggle?.setAttribute('aria-expanded', 'false');
            document.querySelector('[data-category-menu]')?.classList.add('grid-rows-[0fr]');
            document.querySelector('[data-category-menu]')?.classList.remove('grid-rows-[1fr]');
        });
    });

    categoryMenuToggle?.addEventListener('click', () => {
        const expanded = categoryMenuToggle.getAttribute('aria-expanded') === 'true';
        categoryMenuToggle.setAttribute('aria-expanded', String(!expanded));
        const menu = document.querySelector('[data-category-menu]');
        menu?.classList.toggle('grid-rows-[1fr]', !expanded);
        menu?.classList.toggle('mt-2', !expanded);
        menu?.classList.toggle('grid-rows-[0fr]', expanded);
        const chevron = categoryMenuToggle.querySelector('[data-category-menu-chevron]');
        chevron?.classList.toggle('rotate-180', !expanded);
    });

    activate(resolveActivePanel());
}
