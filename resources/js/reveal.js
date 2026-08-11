// Port of hooks/useInView.ts + components/ui/{Reveal,RevealStagger}.tsx.
// Progressive enhancement: [data-reveal] elements are visible by default in
// CSS (see resources/css/app.css); this only starts hiding them once it can
// actually observe intersections.
export function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        items.forEach((el) => el.classList.add('is-in-view'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-in-view');
                    observer.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -60px 0px' }
    );

    items.forEach((el) => observer.observe(el));
}
