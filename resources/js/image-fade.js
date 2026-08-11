// Port of hooks/useImageLoaded.ts. Progressive enhancement: images are
// visible by default in CSS; this only fades them in from a skeleton once
// JS confirms it can track the load event.
export function initImageFade() {
    document.querySelectorAll('[data-fade-img]').forEach((img) => {
        const skeleton = img.previousElementSibling?.hasAttribute('data-fade-img-skeleton')
            ? img.previousElementSibling
            : null;

        const markLoaded = () => {
            img.classList.add('is-loaded');
            skeleton?.classList.add('is-loaded');
        };

        if (img.complete && img.naturalWidth > 0) {
            markLoaded();
        } else {
            img.addEventListener('load', markLoaded, { once: true });
        }
    });
}
