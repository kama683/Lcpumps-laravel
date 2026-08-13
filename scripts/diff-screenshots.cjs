#!/usr/bin/env node
/**
 * Pixel-diffs the screenshot pairs produced by compare-screenshots.cjs
 * (same outDir, `${page}_${locale}_${width}_next.png` vs `_laravel.png`)
 * and prints a sorted report instead of requiring someone to eyeball
 * ~450 PNGs by hand. A dimension mismatch (different full-page height)
 * is reported separately from a pixel diff, since pixelmatch can't
 * compare differently-sized images at all.
 *
 * Usage: node scripts/diff-screenshots.cjs [--dir=path] [--threshold=0.02]
 */
const fs = require('fs');
const path = require('path');
const { PNG } = require('pngjs');
// pixelmatch v7+ is ESM-only; this script is CJS, so it has to come in via
// a dynamic import from inside the async main() below rather than a plain
// top-level require().
let pixelmatch;

function parseArgs() {
  const args = Object.fromEntries(
    process.argv.slice(2).map((arg) => {
      const [key, value] = arg.replace(/^--/, '').split('=');
      return [key, value];
    })
  );
  return {
    dir: args.dir || path.resolve(__dirname, '../storage/app/screenshot-compare'),
    threshold: args.threshold ? Number(args.threshold) : 0.02, // 2% of pixels
  };
}

function readPng(file) {
  return PNG.sync.read(fs.readFileSync(file));
}

async function main() {
  pixelmatch = (await import('pixelmatch')).default;

  const { dir, threshold } = parseArgs();
  const files = fs.readdirSync(dir);
  const nextFiles = files.filter((f) => f.endsWith('_next.png'));

  const results = [];

  for (const nextFile of nextFiles) {
    const base = nextFile.slice(0, -'_next.png'.length);
    const laravelFile = `${base}_laravel.png`;
    const nextPath = path.join(dir, nextFile);
    const laravelPath = path.join(dir, laravelFile);

    if (!fs.existsSync(laravelPath)) {
      results.push({ base, status: 'MISSING_LARAVEL', diffPct: null });
      continue;
    }

    const img1 = readPng(nextPath);
    const img2 = readPng(laravelPath);

    if (img1.width !== img2.width || img1.height !== img2.height) {
      results.push({
        base,
        status: 'SIZE_MISMATCH',
        diffPct: null,
        detail: `next=${img1.width}x${img1.height} laravel=${img2.width}x${img2.height}`,
      });
      continue;
    }

    const { width, height } = img1;
    const diffImg = new PNG({ width, height });
    const diffPixels = pixelmatch(img1.data, img2.data, diffImg.data, width, height, {
      threshold: 0.1,
    });
    const totalPixels = width * height;
    const diffPct = diffPixels / totalPixels;

    if (diffPct > threshold) {
      const diffPath = path.join(dir, `${base}_diff.png`);
      fs.writeFileSync(diffPath, PNG.sync.write(diffImg));
      results.push({ base, status: 'DIFF', diffPct, detail: `${diffPixels}/${totalPixels}px` });
    } else {
      results.push({ base, status: 'MATCH', diffPct });
    }
  }

  const missing = nextFiles.filter((f) => {
    const base = f.slice(0, -'_next.png'.length);
    return !results.find((r) => r.base === base);
  });

  results.sort((a, b) => (b.diffPct || 0) - (a.diffPct || 0));

  const diffs = results.filter((r) => r.status === 'DIFF' || r.status === 'SIZE_MISMATCH' || r.status === 'MISSING_LARAVEL');
  const matches = results.filter((r) => r.status === 'MATCH');

  console.log(`=== ${diffs.length} pair(s) differ, ${matches.length} match (threshold ${(threshold * 100).toFixed(1)}%) ===\n`);
  for (const r of diffs) {
    console.log(`${r.status.padEnd(15)} ${r.base}${r.diffPct !== null ? ` (${(r.diffPct * 100).toFixed(2)}%)` : ''} ${r.detail || ''}`);
  }

  fs.writeFileSync(
    path.join(dir, '_diff-report.json'),
    JSON.stringify({ threshold, results }, null, 2)
  );
  console.log(`\nFull report: ${path.join(dir, '_diff-report.json')}`);
}

main();
