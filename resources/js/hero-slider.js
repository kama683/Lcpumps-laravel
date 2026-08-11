// Port of components/ui/HeroSlider.tsx: fullscreen crossfade + scroll-linked
// parallax. Server-rendered markup already shows slide 0 at full opacity, so
// without JS this is simply a static first slide.
const SLIDE_INTERVAL_MS = 6000;
const PARALLAX_STRENGTH = 0.18;
const PARALLAX_MAX_PX = 70;

export function initHeroSlider() {
    document.querySelectorAll('[data-hero-slider]').forEach((root) => {
        const slides = root.querySelectorAll('[data-slide]');
        if (slides.length > 1) {
            let index = 0;
            setInterval(() => {
                index = (index + 1) % slides.length;
                slides.forEach((slide, i) => {
                    slide.style.opacity = i === index ? '1' : '0';
                });
            }, SLIDE_INTERVAL_MS);
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        let raf = 0;
        const onScroll = () => {
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(() => {
                const offset = Math.min(window.scrollY * PARALLAX_STRENGTH, PARALLAX_MAX_PX);
                slides.forEach((slide) => {
                    slide.style.transform = `translate3d(0, ${offset}px, 0) scale(1.08)`;
                });
            });
        };
        window.addEventListener('scroll', onScroll, { passive: true });
    });
}
