// Port of the cursor-tracking 3D tilt in components/ui/ProductImage.tsx
// (opt-in via the `tilt` prop — only the product detail page's large hero
// shot uses it, not catalog grid cards).
const TILT_MAX_DEG = 8;
const TILT_RESET = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';

export function initTilt() {
    document.querySelectorAll('[data-tilt]').forEach((container) => {
        const img = container.querySelector('img');
        if (!img) return;

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reducedMotion) return;

        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * TILT_MAX_DEG * 2;
            const rotateX = (0.5 - py) * TILT_MAX_DEG * 2;
            img.style.transitionDuration = '400ms, 0ms';
            img.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.03, 1.03, 1.03)`;
        });

        container.addEventListener('mouseleave', () => {
            img.style.transitionDuration = '400ms, 300ms';
            img.style.transform = TILT_RESET;
        });
    });
}
