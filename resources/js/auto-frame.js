// Port of hooks/useAutoFrameScale.ts — see that file's comment for the "why":
// some catalog photos are letterboxed by object-contain and end up occupying
// only a sliver of the card, so this samples the loaded image onto a small
// offscreen canvas, finds the bounding box of non-near-white pixels, and
// scales up only the genuinely small renders (capped so content never grows
// past the card edge). Any failure just leaves the image at its natural size.
const SAMPLE_SIZE = 48;
const NEAR_WHITE_THRESHOLD = 245;
const TARGET_AREA_FRACTION = 0.42;
const HARD_MAX_SCALE = 1.6;
const SAFE_EDGE_MARGIN = 0.94;

function parseAspectRatio(value) {
    const [w, h] = value.split('/').map(Number);
    if (!w || !h) return 4 / 3;
    return w / h;
}

function analyze(img, aspectRatio) {
    try {
        if (!img.naturalWidth || !img.naturalHeight) return;
        const canvas = document.createElement('canvas');
        canvas.width = SAMPLE_SIZE;
        canvas.height = SAMPLE_SIZE;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        ctx.drawImage(img, 0, 0, SAMPLE_SIZE, SAMPLE_SIZE);
        const { data } = ctx.getImageData(0, 0, SAMPLE_SIZE, SAMPLE_SIZE);

        let minX = SAMPLE_SIZE;
        let minY = SAMPLE_SIZE;
        let maxX = 0;
        let maxY = 0;
        let found = false;

        for (let y = 0; y < SAMPLE_SIZE; y++) {
            for (let x = 0; x < SAMPLE_SIZE; x++) {
                const i = (y * SAMPLE_SIZE + x) * 4;
                const a = data[i + 3];
                if (a < 16) continue;
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                if (r > NEAR_WHITE_THRESHOLD && g > NEAR_WHITE_THRESHOLD && b > NEAR_WHITE_THRESHOLD) continue;
                found = true;
                if (x < minX) minX = x;
                if (x > maxX) maxX = x;
                if (y < minY) minY = y;
                if (y > maxY) maxY = y;
            }
        }
        if (!found) return;

        const boxW = (maxX - minX + 1) / SAMPLE_SIZE;
        const boxH = (maxY - minY + 1) / SAMPLE_SIZE;

        const imgAspect = img.naturalWidth / img.naturalHeight;
        const containerAspect = parseAspectRatio(aspectRatio);
        let renderedW;
        let renderedH;
        if (imgAspect >= containerAspect) {
            renderedW = containerAspect;
            renderedH = containerAspect / imgAspect;
        } else {
            renderedH = 1;
            renderedW = imgAspect;
        }

        const contentWFrac = (boxW * renderedW) / containerAspect;
        const contentHFrac = boxH * renderedH;
        const areaFraction = contentWFrac * contentHFrac;
        if (areaFraction <= 0 || areaFraction >= TARGET_AREA_FRACTION) return;

        const wanted = Math.sqrt(TARGET_AREA_FRACTION / areaFraction);
        const safeMax = SAFE_EDGE_MARGIN / Math.max(contentWFrac, contentHFrac);
        const factor = Math.min(wanted, safeMax, HARD_MAX_SCALE);
        if (factor > 1.05) {
            img.style.setProperty('--auto-scale', String(factor));
        }
    } catch {
        // Canvas sampling failed for any reason — leave the image untouched.
    }
}

export function initAutoFrame() {
    document.querySelectorAll('[data-auto-frame]').forEach((img) => {
        const aspectRatio = img.dataset.aspectRatio || '4/3';
        if (img.complete) analyze(img, aspectRatio);
        else img.addEventListener('load', () => analyze(img, aspectRatio), { once: true });
    });
}
