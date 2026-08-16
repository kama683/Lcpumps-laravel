# Deploy runbook — LCpumps Laravel port to PS.kz

Written for the **confirmed, real** PS.kz account (tariff M, Plesk/CloudLinux,
Apache+FastCGI, PHP 8.3.33) — not general assumptions. Every command,
path, and config value below has been tested locally against a clean
`--no-dev` build with this exact `.env` before being written down here.
Follow the steps in order; each one assumes the previous one is done.

**The live site (currently served from Railway) keeps working throughout
every step below except the last.** Nothing here touches DNS until the
final section, which is explicitly the only step visitors are affected by.
If anything earlier goes wrong, the client's site is completely unaffected
— fix and retry, no rush.

---

## Environment reference (confirmed, not guessed)

| | |
|---|---|
| Hosting | PS.kz, shared tariff M, Plesk on CloudLinux, Apache reverse-proxied, FastCGI |
| PHP | 8.3.33, OPcache on (`validate_timestamps=On`, `revalidate_freq=2`) |
| Extensions | `intl`, `gd` (WebP+AVIF), `bcmath`, `mbstring`, `zip`, `pdo_mysql`, `fileinfo`, `imagick`, `redis` (ext only, no server confirmed), `openssl`, `curl`, `soap`, `xsl` — all of composer.json's requirements are covered |
| Limits | `memory_limit=256M`, `post_max_size=64M`, `upload_max_filesize=32M`, `max_execution_time=120`, timezone `Asia/Almaty` |
| Disabled functions | `exec, shell_exec, system, popen, proc_open, pcntl_*, posix_*, ...` — `symlink` is **not** in this list, confirmed allowed |
| SSH | **None.** Every console command below runs via Plesk Scheduled Tasks |
| FTP root | `/var/www/vhosts/lcpumps.kz/` (user `lcpumpsk`) |
| Current document root | `/var/www/vhosts/lcpumps.kz/httpdocs` — will be repointed, see Step 1 |
| `open_basedir` | `/var/www/vhosts/lcpumps.kz/:/tmp/` — everything must live inside this |
| Proxy headers | `X-Real-IP` carries the visitor's real IP; `REMOTE_ADDR` is the proxy's own address (already handled in code, see Step 8) |
| Mail | `info@lcpumps.kz` mailbox exists, SMTP port 465 with implicit SSL (`smtps`, not STARTTLS) |
| Server IP | `195.210.46.99` (for the DNS cutover at the end) |

---

## Step 1 — Server directory structure

```
/var/www/vhosts/lcpumps.kz/
├── httpdocs/          ← existing Plesk default folder, left in place but unused
└── laravel/           ← the whole app lands here
    ├── app/ bootstrap/ config/ database/ lang/ public/ resources/ routes/ storage/ vendor/
    └── .env            ← created in Step 4, never uploaded
```

`laravel/` sits **next to** `httpdocs/`, not inside it — this is the
standard pattern for Laravel on Plesk shared hosting, and it's what lets
`storage/` (with the private-attachments disk) end up outside the web
root automatically, for free, just from the directory math: document root
becomes `laravel/public/`, and `laravel/storage/` is a sibling of
`laravel/public/`, never reachable through it. No separate "make sure
attachments aren't exposed" trick needed — confirmed by the layout itself,
re-verified for real after upload in Step 9.

**Change the document root now** (do this before uploading, so nothing is
briefly served from the wrong place): Plesk → **Сайты и домены** →
`lcpumps.kz` → **Настройки хостinga** → **Корневой каталог документов** →
set to `laravel/public`. Save.

`httpdocs/` becomes unused once this is saved — safe to leave as-is
(nothing points at it anymore) or empty it later; not required for the
deploy to work.

---

## Step 2 — Build package (already done, ready to upload)

Built and tested locally — this isn't a "you do this" step, it's what
was already produced:

```bash
composer install --no-dev --optimize-autoloader   # vendor/ shrinks 78MB -> 36MB
```

Verified after the `--no-dev` install, not assumed:
- `bootstrap/cache/packages.php`/`services.php` regenerate automatically
  (Composer's `post-autoload-dump` hook runs `artisan package:discover`) and
  correctly **no longer reference** `laravel/pail`, `nunomaduro/collision`,
  or any other dev-only package — confirmed by grepping the regenerated
  files. Booting the app straight after `--no-dev` with those stale
  references still in `bootstrap/cache/` would otherwise be a hard
  "class not found" 500 on every request.
- The app was actually served (`php artisan serve` against this exact
  `--no-dev` vendor/) and every key route hit over real HTTP, all 200:
  `/`, `/kk`, `/en`, `/zh`, `/products`, `/products/pumps`, `/admin/login`,
  `/sitemap.xml`, `/robots.txt`, `/faq`, `/contact`, `/applications`,
  `/solutions` — with `config:cache` + `view:cache` + `route:cache` **all
  active at once**, the same state production will run in.
- **`route:cache` works, contrary to the usual assumption about closures.**
  `routes/web.php`'s two closure routes (`/ru`, `/ru/{path}` — the
  next-intl-parity redirect) were expected to break `route:cache`
  (historically true for Laravel, and still true for many frameworks'
  route cachers). Tested directly: `php artisan route:cache` succeeds,
  and `/ru` and `/ru/products/pumps?foo=bar` (query string included) both
  still redirect correctly with the cache active. Laravel 13 ships
  `laravel/serializable-closure` as a core dependency of the framework
  itself (confirmed present in the `--no-dev` vendor/, not a dev-only
  package), which is what makes this work now. **Recommendation: include
  `route:cache` in Step 6** — it's a real, working performance win on a
  memory/CPU-constrained shared tariff, not something to skip out of an
  outdated caution. Re-verify `/ru` once more after the real deploy
  regardless (Step 7's checklist covers this) — this was proven on a local
  `php artisan serve`, not this exact Apache+FastCGI+OPcache stack, and
  the one meaningfully different variable (closure serialization) is
  PHP-version-and-package behavior, not web-server behavior, so it should
  carry over, but "should" isn't "verified on the real box."

**Frontend build**: `public/build/` is already committed and current —
no source file that affects it (CSS classes, JS) changed since the last
`npm run build`, confirmed by an empty `git diff` after rebuilding. Nothing
to do here.

---

## Step 3 — Upload

**One file: `lcpumps-laravel-deploy.zip`**, ~161 MB, sitting at
`C:\Users\kamro\Desktop\bellery pumps\lcpumps-laravel-deploy.zip` on this
machine — built from a clean `git archive` (not the live working copy, so
no stray local files), `composer install --no-dev` applied, then stripped
of everything with no purpose on the server: `tests/`, `phpunit.xml`,
`scripts/` (Node-based screenshot-diff tooling), `package.json`/
`package-lock.json`/`vite.config.js`/`.npmrc` (no Node on the server, and
`public/build/` is already the compiled output), `.gitattributes`,
`.editorconfig`, and any local `.env`. `vendor/`, `public/build/`,
`public/assets/`, `lang/`, all migrations/seeders, `composer.lock`, and
the project `.md` docs are all included.

**Upload it via FTP to the vhost root** (`lcpumpsk`'s FTP home, i.e.
directly next to the existing `httpdocs/` folder) — one binary file, not
thousands of small ones, which on this connection is meaningfully faster
than uploading the tree file-by-file.

**Then extract it in Plesk, not via FTP**: Plesk → **Файлы** (File
Manager) → navigate to the vhost root → select
`lcpumps-laravel-deploy.zip` → **Извлечь файлы** (Extract). The zip's
top-level entry is already named `laravel/`, so extracting at the vhost
root produces `laravel/app`, `laravel/public`, etc. directly — no nested
extra folder to flatten. After confirming the extraction succeeded
(File Manager should show `laravel/` with `app/`, `vendor/`, `public/`,
etc. inside it), **delete the zip** from the server (File Manager →
select it → Remove) — no reason to leave a 161 MB archive of the source
sitting in the web space.

**Verify before continuing**: `https://<server-IP-or-temp-domain>/composer.json`
must 404 (proves the document root change from Step 1 actually took
effect and the app root isn't directly served) — check this now, before
`.env` even exists, since a leak here would otherwise briefly expose
nothing sensitive yet, but it's the cheapest moment to catch a document
root mistake before secrets are in play.

---

## Step 4 — File permissions

The web server user (Plesk's per-domain system user under CloudLinux —
usually named after the subscription, check **Сайты и домены** →
`lcpumps.kz` → **Хостинг** for the exact name if curious, not required to
proceed) needs write access to `laravel/storage/` (all of it — logs,
sessions, compiled views, uploaded files) and `laravel/bootstrap/cache/`.

Via Plesk File Manager (no console access, so no `chmod` command — use
the UI): select `storage/` → **Изменить права доступа** (Change
Permissions) → enable write for the owner, apply **recursively** to all
files/subfolders. Repeat for `bootstrap/cache/`. On CloudLinux/Plesk,
PHP-FPM runs as the domain's own system user (not a separate `www-data`),
so owner-write (755-equivalent through the UI) is normally enough — no
need for the looser world-writable permissions sometimes advised for
generic Apache setups.

**Verify after Step 5** (once `.env` exists and a page is loaded): a
permissions problem here surfaces immediately as a 500 error, specifically
`file_put_contents(): Failed to open stream: Permission denied` pointing
at `storage/logs/laravel.log` or `bootstrap/cache/*.php` — if that
happens, come back here first.

---

## Step 5 — `.env`

A ready-to-use file is at `laravel/.env.production` on this machine (also
gitignored, never committed). **Rename it to `.env` and upload it directly
to `laravel/.env` on the server** (Plesk File Manager upload, or as the
one deliberate exception to "don't FTP local files" — either way, never
via git).

Two things that were flagged in an earlier pass are now resolved:

1. **`SESSION_LIFETIME=120`** (2 hours, Laravel's stock default) —
   **confirmed intentional**, not reverting to `.env.example`'s documented
   `10080` (7 days). That 7-day figure was a forced tradeoff for the
   source's stateless, unrevokable JWT sessions; a server-side session (what
   this app actually uses) can be revoked at will, so there's no reason to
   carry over that same long lifetime — 2 hours is the more appropriate
   default here. Only the admin panel uses sessions, so this doesn't affect
   visitors.
2. **`TRUSTED_PROXIES=*`** (trust the direct TCP peer unconditionally) —
   **not** a specific IP, and not a shortcut. A phpinfo dump from the real
   server confirmed `REMOTE_ADDR` is `195.210.46.99` (the proxy), not
   `127.0.0.1` as first guessed — but the value isn't `195.210.46.99`
   either. Reasoning: PHP-FPM on this shared tariff has no ingress path
   except through PS.kz's one Apache proxy, so an IP-allowlist here isn't
   actually the security boundary — nothing can reach PHP-FPM pretending to
   be a different, untrusted proxy in the first place, whether the setting
   says `*` or a specific address. The real boundary is
   `App\Http\Middleware\NormalizeRealIpHeader` (already in the codebase,
   already tested — see its docblock): it always overwrites
   `X-Forwarded-For` from `X-Real-IP` when present, and **strips
   `X-Forwarded-For` entirely when `X-Real-IP` is absent** — confirmed via
   the same phpinfo dump that PS.kz's proxy sends `X-Real-IP` and never
   `X-Forwarded-For` itself, so any `X-Forwarded-For` arriving on its own
   can only be a client trying to inject one directly, never a legitimate
   chain. Full verification of this is Step 8 — don't skip it, it's the
   thing actually standing between an attacker and the rate limiters, not
   the `TRUSTED_PROXIES` value.

---

## Step 6 — Console commands (Plesk Scheduled Tasks, no SSH)

Plesk → **Сайты и домены** → `lcpumps.kz` → **Задачи планировщика** (or
the top-level **Инструменты и настройки** → **Задачи планировщика**,
depending on Plesk version) → **Добавить задачу**.

For every command below, the task shape is the same:
- **Тип задачи / Run**: "Запуск PHP-скрипта" (Run a PHP script) if
  offered as a distinct type; otherwise "Выполнить команду" with the PHP
  binary as the command and the script+args as parameters — Plesk's
  wording varies slightly by version, match by intent, not by hunting for
  an exact label.
- **Версия PHP**: select **8.3** explicitly — don't leave it on "default"
  if the account has more than one PHP version available.
- **PHP-скрипт / Script**: `/var/www/vhosts/lcpumps.kz/laravel/artisan`
  (the full path — `open_basedir` requires it to resolve inside the
  allowed tree, a relative path risks resolving against the wrong cwd)
- **Дополнительные параметры / Arguments**: the command name + flags from
  the table below, nothing else
- **Run once now**, don't leave it on a recurring schedule — these are
  one-shot setup commands

Run in this exact order — each depends on the previous one having
succeeded:

| # | Arguments | What it does | How to confirm it worked |
|---|---|---|---|
| 1 | `key:generate --force` | Writes `APP_KEY` into `.env` on the server | Re-open `laravel/.env` in File Manager, confirm `APP_KEY=base64:...` is no longer empty |
| 2 | `migrate --force` | Creates all tables | Task log shows a list of migration names with "DONE"; if it errors, nothing else below will work — stop and fix here first |
| 3 | `db:seed --force` | Populates the product catalog (`CatalogSeeder`) | Task log shows no error; real check is Step 7's checklist (`/products` shows real products, not empty) |
| 4 | `admin:seed-user --username=<pick one> --password=<pick one, 8+ chars>` | Creates the admin account | Task log: `Admin user "..." is set.` Real check: log in at `/admin/login` |
| 5 | `storage:link` | Symlinks `public/storage` → `storage/app/public` (product images) | Task log: `The [public/storage] link has been connected...`; real check: File Manager shows `laravel/public/storage` as a symlink, not a missing/broken one — `symlink` is confirmed allowed on this account, so this should just work |
| 6 | `config:cache` | Caches `.env`/config into one file — meaningful win on a 256 MB-RAM/1-CPU tariff | Task log: `Configuration cached successfully.` |
| 7 | `view:cache` | Precompiles all Blade templates | Task log: `Blade templates cached successfully.` |
| 8 | `route:cache` | Caches route definitions — tested working locally including the `/ru` closures, see Step 2 | Task log: `Routes cached successfully.`; real check: visit `/ru` on the live site, confirm it redirects to `/` and not a 500 |

**After every step, check that task's execution log in Plesk before moving
to the next one** — a failed migration leaves the DB in a partial state
that the next command fails against differently, harder to diagnose than
catching it at the source.

**Delete every one of these 8 tasks once confirmed working** — Plesk →
Задачи планировщика → select each → **Удалить** (Remove). In particular,
don't leave the `admin:seed-user` task sitting in the list with real
credentials in its argument history longer than needed to run it once.

**If `.env` is ever edited again after this point**, re-run `config:cache`
— it's a snapshot, not re-read live from `.env` after being cached. (Step 8
adds/removes a temporary *route*, not an `.env` change, so it needs
`route:cache` re-run if that's active, not `config:cache`.)

---

## Step 7 — First checkpoint: does the site actually work

Before touching mail, limits, or proxies — confirm the baseline works, so
later failures are attributable to what you just changed, not stacked on
top of an already-broken base:

- [ ] `/`, `/kk`, `/en`, `/zh` all load (use the server's own domain
      once DNS-independent access is possible — e.g. a Plesk-assigned
      temporary preview URL, or the server IP with a `Host` header
      override — since real DNS isn't pointed here yet)
- [ ] `/products` and `/products/pumps` show real products, not an empty
      grid (confirms Step 6.3's seed actually ran)
- [ ] `/admin/login` loads and logging in with Step 6.4's credentials
      works
- [ ] `/ru` redirects to `/` (confirms Step 6.8's route cache didn't
      break the closure routes for real, not just locally)
- [ ] A product image shows on a product page (confirms Step 6.5's
      symlink resolves)

If any of these fail, this is the point to stop and fix — everything
below assumes a working baseline.

---

## Step 8 — `TrustProxies` — verify it resolves the real visitor, and that spoofing doesn't work

Nothing to configure here — `TRUSTED_PROXIES=*` and
`NormalizeRealIpHeader` are already in place (Step 5). This step is purely
verification: prove `$request->ip()` actually resolves to the real
visitor in production, and prove a spoofed header can't override it.
Both matter — the rate limiters on the contact form and admin login are
only as good as this.

**1. Confirm the real IP resolves correctly — from an actual outside
network, not this machine:**

Add a throwaway route that dumps the resolved IP:

```php
// routes/web.php — temporary, remove after this check
Route::get('/whoami-debug', fn () => request()->ip());
```

Upload just that one change (or add it via Plesk's File Manager editor
directly on the server, faster than a redeploy for a one-line temporary
route), then re-run `config:cache`/`route:cache` if they're active. Load
`https://www.lcpumps.kz/whoami-debug` **from your phone, on mobile data**
— not this Wi-Fi, not the same network as the server — so the IP it should
show is a real, independently-known value (check `https://2ip.kz` on the
same phone/connection first, or your carrier IP if you know it, then
compare). It must show that value, not `195.210.46.99` (the proxy) and not
`127.0.0.1`. If it shows the proxy's IP instead, `X-Real-IP` isn't arriving
the way assumed, or `NormalizeRealIpHeader` isn't running before
`TrustProxies` — re-check `bootstrap/app.php`'s middleware order.

**Remove the temporary route once confirmed** — don't leave an
unauthenticated IP-disclosure endpoint live.

**2. Confirm a spoofed header can't override it — this is the part that
actually protects the rate limiters:**

```bash
curl -H "X-Forwarded-For: 6.6.6.6" https://www.lcpumps.kz/whoami-debug
```

(Re-add the temporary route for this check if already removed, or combine
both checks in the same window before removing it.) This must **not**
return `6.6.6.6` — if it does, `NormalizeRealIpHeader` isn't stripping a
directly-injected `X-Forwarded-For` the way `TrustedProxyIpTest`'s
`test_spoofed_x_forwarded_for_is_stripped_under_trust_all_when_no_x_real_ip_present`
proves it should, and the rate limiters are bypassable — a spoofer could
get a fresh bucket on every request just by changing that header.

**3. Confirm the rate limiter itself still triggers** with the (now
verified-correct) real-IP resolution: hit `/admin/login` 6 times in a row
from the same connection and confirm the 6th attempt shows "Слишком много
попыток" — proves the limiter is both *not bypassable* (step 2) and
*actually firing* (this step) rather than being silently defeated in the
other direction (e.g. everyone sharing one bucket because IP resolution
broke entirely).

---

## Step 9 — Verify private attachments aren't reachable

Should already hold by construction (Step 1's directory layout puts
`storage/` outside `public/` entirely), but confirm on the real
deployment, not just in theory: after a real contact-form submission with
an attachment exists (Step 10 produces one), try
`https://www.lcpumps.kz/storage/private-uploads/<anything>` — must 404.
If it instead serves something, the document root from Step 1 is wrong —
re-check that before anything else.

---

## Step 10 — Mail: verify actual delivery

`.env` already has `MAIL_MAILER=smtp`, `MAIL_PORT=465`, `MAIL_SCHEME=smtps`
(implicit SSL, not STARTTLS — matches the mailbox's real config, already
correct, no action needed here beyond what Step 5 uploaded).

**Verify delivery end to end, not just that the form doesn't error**:
submit `https://www.lcpumps.kz/contact` for real, then confirm **both**:

1. The submission appears in the admin panel's Requests list (proves the
   DB write path, independent of mail).
2. An email actually arrives at `info@lcpumps.kz`. `ContactSubmissionHandler`
   deliberately doesn't let a failed notification email block the DB save
   (there's a test for this), which means a broken SMTP config produces
   zero emails while the admin panel still looks completely fine — the
   only way to catch that is checking the inbox, not the panel.

If nothing arrives, check `storage/logs/laravel.log` for the SMTP error —
the most likely mistake at this specific config is `MAIL_SCHEME` reverting
to `null`/`tls` (STARTTLS) instead of staying `smtps`, since port 465
specifically needs implicit SSL, not STARTTLS.

---

## Step 11 — Upload/body-size limits — already sufficient, one thing to still verify

PS.kz's PHP limits (`upload_max_filesize=32M`, `post_max_size=64M`) are
already comfortably above the app's own caps (15 MB/file, 40 MB total —
`ContactSubmissionHandler`) — no PHP-level change needed, unlike a from-
scratch Plesk setup where these often start too low.

**What's still unverified**: whether Apache/Plesk's own request-body cap
(separate from the PHP settings, and not covered by the numbers PS.kz
already confirmed) is set low enough to interfere — Plesk sometimes sets
an nginx- or Apache-level cap independent of PHP's. Verify with a real
upload: submit the contact form with an attachment between 32–40 MB
total. If it fails with a generic proxy/server error page instead of the
app's own "too large" message, Plesk → **Сайты и домены** → `lcpumps.kz`
→ **Apache и nginx настройки** → raise the additional-directives body-size
cap to match (`64m`, matching `post_max_size`).

---

## DNS cutover — last step, only after everything above is verified

`www.lcpumps.kz` currently resolves via a **CNAME pointing at Railway**
(the platform hosting the live Next.js site); bare `lcpumps.kz` is a 302
to `www`. Cutting over means deleting that CNAME and creating an **A
record pointing at `195.210.46.99`** — CNAME and A can't coexist on the
same name, so this is delete-and-recreate, not an edit.

**Order, strictly:**

1. Only after every step above is verified working at the server's own
   IP/temporary URL — the new site should be fully checked out before DNS
   ever sends real visitors at it.
2. Lower the current CNAME's TTL a day ahead if the DNS provider allows
   it, to shorten the window where different visitors see different
   sites during propagation.
3. Delete the CNAME, create the A record → `195.210.46.99`.
4. Monitor from more than one network/resolver until
   `https://www.lcpumps.kz` consistently resolves to the new IP.
5. **Decommission Railway only after DNS has fully propagated and the
   acceptance checklist below has been re-run against the live
   `www.lcpumps.kz` domain** (not just the temporary IP) — until then,
   Railway is the rollback path if the cutover needs to be reverted.

---

## Acceptance checklist — run after DNS cutover, against the live `www.lcpumps.kz`

- [ ] Home page opens on all 4 locales: `/`, `/kk`, `/en`, `/zh`
- [ ] Product catalog shows real products — `/products` and
      `/products/pumps`
- [ ] A product detail page opens and shows its images/specs
- [ ] Contact form submission arrives **both** in the admin panel and as
      an email to `info@lcpumps.kz`
- [ ] Admin login works
- [ ] A product image can be uploaded through the admin panel
- [ ] A contact-form attachment can be downloaded from the admin panel
- [ ] SSL certificate is valid — no browser warning on
      `https://www.lcpumps.kz`
- [ ] `/sitemap.xml` and `/robots.txt` both return successfully
- [ ] `https://lcpumps.kz` (no `www`) redirects to `https://www.lcpumps.kz`
