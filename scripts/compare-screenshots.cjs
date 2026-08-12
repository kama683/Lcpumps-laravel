#!/usr/bin/env node
/**
 * Side-by-side visual comparison against the Next.js reference site —
 * screenshots the same paths on both, across all 4 locales and a set of
 * viewport widths. Used for every Stage 5 batch's sign-off; run with no
 * arguments to sweep every page listed in PAGES once all of Stage 5 is
 * done (Stage 10 final acceptance).
 *
 * Requires: `npm install` (playwright-core is a devDependency) and a
 * locally installed Chrome/Chromium. Both reference servers must already
 * be running:
 *   - Next.js dev server (site/):       npm run dev   (default :3000)
 *   - Laravel dev server (this repo):   php artisan serve --port=8010
 *
 * Usage:
 *   npm run compare
 *   npm run compare -- --pages=home,pumps
 *   npm run compare -- --locales=ru,en --widths=375,1440
 *   npm run compare -- --next=http://localhost:3000 --laravel=http://127.0.0.1:8010
 *
 * CHROME_PATH env var overrides the auto-detected Chrome executable.
 */
const { chromium } = require('playwright-core');
const path = require('path');
const fs = require('fs');

// path === '' means the site root ("/"). Add an entry here as each Stage 5
// page lands — this list is meant to grow to all ~18 pages by Stage 10.
const PAGES = {
  home: '',
  products: '/products',
  pumps: '/products/pumps',
  valves: '/products/valves',
  control: '/products/control',
  water: '/products/water',
  productDetail: '/products/sls',
  projects: '/projects',
  projectDetail: '/project/bird-nest',
  applications: '/applications',
  waterWastewater: '/applications/water-wastewater',
  aboutUs: '/about-us',
  aboutUsPrimary: '/about-us/our-company',
  aboutUsExtra: '/about-us/certifications',
  services: '/services',
  solutions: '/solutions',
  faq: '/faq',
  news: '/news',
};

const DEFAULT_LOCALES = [
  { code: 'ru', prefix: '' },
  { code: 'kk', prefix: '/kk' },
  { code: 'en', prefix: '/en' },
  { code: 'zh', prefix: '/zh' },
];

const DEFAULT_WIDTHS = [375, 901, 1440];

function findChrome() {
  if (process.env.CHROME_PATH) return process.env.CHROME_PATH;
  const candidates = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium-browser',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
  ];
  for (const c of candidates) {
    if (fs.existsSync(c)) return c;
  }
  throw new Error('Could not find a Chrome install. Set CHROME_PATH env var.');
}

function parseArgs() {
  const args = Object.fromEntries(
    process.argv.slice(2).map((arg) => {
      const [key, value] = arg.replace(/^--/, '').split('=');
      return [key, value];
    })
  );

  const pageNames = args.pages ? args.pages.split(',') : Object.keys(PAGES);
  const localeCodes = args.locales ? args.locales.split(',') : DEFAULT_LOCALES.map((l) => l.code);
  const widths = args.widths ? args.widths.split(',').map(Number) : DEFAULT_WIDTHS;

  return {
    pages: pageNames.map((name) => {
      if (!(name in PAGES)) throw new Error(`Unknown page "${name}". Known: ${Object.keys(PAGES).join(', ')}`);
      return { name, path: PAGES[name] };
    }),
    locales: DEFAULT_LOCALES.filter((l) => localeCodes.includes(l.code)),
    widths,
    nextBase: args.next || 'http://localhost:3000',
    laravelBase: args.laravel || 'http://127.0.0.1:8010',
    outDir: args.out || path.resolve(__dirname, '../storage/app/screenshot-compare'),
  };
}

/** Scrolls through the page in steps so scroll-triggered reveal animations
 * (present on both sites) have fired before the full-page screenshot. */
async function scrollThrough(page) {
  const height = await page.evaluate(() => document.body.scrollHeight);
  for (let y = 0; y < height; y += 400) {
    await page.evaluate((yy) => window.scrollTo(0, yy), y);
    await page.waitForTimeout(120);
  }
  await page.waitForTimeout(400);
}

async function main() {
  const { pages, locales, widths, nextBase, laravelBase, outDir } = parseArgs();
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ executablePath: findChrome(), headless: true });
  const targets = [
    { name: 'next', base: nextBase },
    { name: 'laravel', base: laravelBase },
  ];

  let failures = 0;

  for (const width of widths) {
    const context = await browser.newContext({ viewport: { width, height: 900 } });
    const page = await context.newPage();

    for (const target of targets) {
      for (const locale of locales) {
        for (const p of pages) {
          const url = `${target.base}${locale.prefix}${p.path === '' ? '/' : p.path}`;
          const file = path.join(outDir, `${p.name}_${locale.code}_${width}_${target.name}.png`);
          try {
            await page.goto(url, { waitUntil: 'networkidle', timeout: 20000 });
            await scrollThrough(page);
            await page.screenshot({ path: file, fullPage: true });
            console.log(`OK   ${url} -> ${path.basename(file)}`);
          } catch (err) {
            console.log(`FAIL ${url} -> ${err.message}`);
            failures++;
          }
        }
      }
    }
    await context.close();
  }

  await browser.close();
  console.log(`\n${failures === 0 ? 'All captures succeeded.' : `${failures} capture(s) failed.`} Output: ${outDir}`);
  process.exitCode = failures === 0 ? 0 : 1;
}

main();
