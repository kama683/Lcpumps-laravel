// Port of components/ui/StatGrid.tsx's StatValue count-up.
const COUNT_UP_DURATION_MS = 900;

function parseLeadingNumber(raw) {
    const match = raw.match(/^(-?[\d\s.,]*\d)([\s\S]*)$/);
    if (!match) return null;
    const numStr = match[1];
    const suffix = match[2];
    const target = Number.parseInt(numStr.replace(/[\s.,]/g, ''), 10);
    if (Number.isNaN(target)) return null;
    return { target, suffix, numStr };
}

function formatGrouped(n, sample) {
    const sepMatch = sample.match(/[^\d]/);
    const sep = sepMatch ? sepMatch[0] : '';
    const digits = Math.round(n).toString();
    if (!sep) return digits;
    const parts = [];
    for (let i = digits.length; i > 0; i -= 3) {
        parts.unshift(digits.slice(Math.max(0, i - 3), i));
    }
    return parts.join(sep);
}

function animate(el, value) {
    const parsed = parseLeadingNumber(value);
    if (!parsed) return;

    let start = null;
    function tick(ts) {
        if (start === null) start = ts;
        const t = Math.min((ts - start) / COUNT_UP_DURATION_MS, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        if (t >= 1) {
            el.textContent = value;
            return;
        }
        const current = Math.round(parsed.target * eased);
        el.textContent = formatGrouped(current, parsed.numStr) + parsed.suffix;
        requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

export function initStatCountUp() {
    const els = document.querySelectorAll('[data-count-up]');
    if (!els.length) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    // Triggers off the same in-view signal as the stat cell's own
    // [data-reveal] fade — matches the source sharing one useInView call.
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    const target = entry.target.querySelector('[data-count-up]');
                    if (target) animate(target, target.dataset.value);
                    observer.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -60px 0px' }
    );

    els.forEach((el) => {
        observer.observe(el.closest('[data-reveal]') || el);
    });
}
