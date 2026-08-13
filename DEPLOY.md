# Deploy runbook — LCpumps Laravel port to PS.kz

Written so a person with **no prior context on this project** can execute it
step by step, in order, without guessing. Steps are numbered and meant to be
followed top to bottom — later steps assume earlier ones are done (e.g. the
DNS cutover in step 12 assumes steps 1–11 already proved the new site works
on its own IP/hostname).

**The live site (currently served from Railway) must keep working for the
client throughout every step below except the last.** Nothing here touches
Railway or DNS until step 12, and step 12 is explicitly the only step where
visitors are affected. If anything in steps 1–11 goes wrong, the client's
site is completely unaffected — abandon and retry, no rush.

---

## 1. Buy hosting and do the one blocking check

**Regular shared hosting (Plesk "Виртуальный хостинг"), not VPS.** This whole
runbook is built around it — no SSH, admin commands run via Plesk Scheduled
Tasks (step 6/7). VPS would only be worth the extra cost/complexity if this
site needed root access, custom system packages, or traffic high enough to
need dedicated resources — none of which applies here (a B2B industrial
catalog site, not a high-traffic e-commerce store).

Buy PS.kz tariff **M** (5 GB SSD, 768 MB RAM, 1 vCPU, ~1 628 тг/мес as of
2026-08-13 — confirm current pricing at order time, ps.kz changes these).
Reasoning, not a guess:

- **Disk:** the deployed app itself (`vendor/` + `public/build/` + code) is
  ~90 MB measured locally (`vendor/` 78 MB, `public/build/` 9.5 MB) — even
  the smallest **XS** tariff (1 GB) technically fits that. What XS doesn't
  leave room for is growth: product images, contact-form attachments (up to
  40 MB per submission), logs, and Plesk backups all accumulate over the
  site's life. M's 5 GB gives years of headroom for a catalog site this
  size; **S**'s 2 GB is workable but tighter.
- **RAM — the real differentiator between tariffs, not disk:** PS.kz
  documents no limit on databases/mailboxes/domains per account regardless
  of tariff (confirmed via their FAQ) — what actually scales with tariff is
  RAM/CPU, which caps how many PHP-FPM worker processes can run at once. A
  Laravel worker for an app this size typically sits in the tens of MB of
  RSS; **XS's 256 MB** leaves only a handful of workers once Plesk's own
  agent and ImunifyAV scanning are accounted for — workable for a
  low-traffic site but with very little margin for a slow request or a
  crawler burst. **M's 768 MB** gives a comfortable worker pool for this
  site's expected traffic (a niche B2B catalog, not high-volume retail)
  without paying for **L**'s 1024 MB/12 GB (~3 464 тг/мес), which is more
  than this site is likely to need.
- PS.kz's shared-hosting connection limit is **128 simultaneous connections
  per site** (their error-509 documentation) — every asset (image, CSS, JS)
  counts, not just page views, but this is a soft ceiling for a
  moderate-traffic brochure site, not something to design around.

If traffic ends up meaningfully higher than expected after launch, or the
5-fcgi-worker-style contention mentioned in `STATUS.md`/project notes turns
out to bite in practice, upgrading from M to L is a Plesk-side tariff change,
not a redeploy — start at M, watch real usage, upgrade only if needed.

**Before writing a single file to the server**, confirm in the Plesk panel
for the *actual purchased account* — not PS.kz's general marketing page,
which only advertises what their product line supports in principle, not
what's enabled on this account:

- Plesk → Domains → `<domain>` → PHP Settings → version dropdown must offer
  **PHP 8.3 or newer** (`composer.json` requires `"php": "^8.3"`, and the
  code uses 8.3 syntax — typed class constants like
  `private const array X = [...]`). If only 8.2 or lower is available, **stop
  here** — this blocks the whole deploy, not just this step, and needs a
  different tariff or a support ticket to PS.kz before continuing.
- Same PHP Settings page, confirm these extensions are enabled (all
  standard, but Plesk can disable them per-domain):
  - `intl` — localization, used by Laravel's translation system
  - `gd` — image operations, used by product-image upload
  - `bcmath` — arbitrary-precision math, sometimes disabled on shared hosting
  - `mbstring` — multibyte strings, standard, rarely disabled
  - `zip` — archive extraction, used by some Composer dependencies
  - `pdo_mysql` — database driver, **absolutely required**, nothing works
    without it
  - `fileinfo` — MIME-type detection, used by attachment-upload validation

Also note in Plesk whether this account has SSH access at all. Everything
below assumes **no SSH** (typical for this tariff) and uses FTP + Plesk's
web UI + Scheduled Tasks instead. If SSH turns out to be available, steps
3–6 get much simpler (`composer install`, `artisan migrate`, etc. run
directly) — use it if you have it, the FTP/Scheduled-Task path below is the
fallback for when you don't.

---

## 2. Build the release locally

Do this on your own machine, not on the server — there's no SSH to run
`composer install`/`npm run build` remotely.

```bash
# PHP dependencies, production mode (no dev tools, optimized autoloader)
composer install --no-dev --optimize-autoloader

# Frontend assets — compiles resources/css, resources/js into public/build/
npm ci
npm run build
```

Verify `public/build/manifest.json` exists and is recent after the build —
if it's missing, Blade's `@vite(...)` directives in
`components/layouts/app.blade.php` will throw on every page load in
production (no dev-server fallback like local `npm run dev` provides).

**Do not commit or upload `.env`** — it gets created directly on the server
in step 5, with production secrets that never touch git or the FTP client's
saved-file history.

---

## 3. Upload via FTP

No SSH means no `git pull` on the server — upload the whole working tree,
**including the `vendor/` folder** produced by `composer install --no-dev`
in step 2 (there's no server-side Composer to regenerate it) and the
`public/build/` folder produced by `npm run build`.

Skip (don't upload): `node_modules/`, `.git/`, `tests/`, `.env.example`
already covered by step 5, and anything in `storage/` other than the
directory structure itself (no local dev data belongs on prod).

**Document root — this is the step most likely to be gotten wrong**: point
Plesk's document root at `public/`, **not** the repository root. Plesk →
Domains → `<domain>` → Hosting Settings → Document root → set to
`/laravel/public` (or wherever the repo lands relative to the vhost root —
the point is it must resolve to the `public/` subfolder specifically). If
the document root is left at the repo root instead, the entire application
source — including `.env` once it exists — becomes directly downloadable
over HTTP. Verify after upload: `https://www.lcpumps.kz/composer.json`
should 404, not return the file's contents.

---

## 4. File permissions

The web server user (typically `psacln` or similar under Plesk — check
Plesk → Domains → `<domain>` → Hosting Settings for the exact system user)
needs write access to:

- `storage/` (and everything under it — logs, compiled views, session
  files if the driver ever changes off `database`, uploaded files)
- `bootstrap/cache/` (config/route/view cache files Laravel writes here)

FTP uploads often land as read-only or owned by the wrong user depending on
the FTP client. After upload, use Plesk's File Manager (or an FTP client
that supports `chmod`) to set `storage/` and `bootstrap/cache/` to writable
(755 for directories is usually enough on Plesk's shared-hosting setup,
since PHP-FPM runs as the domain's own system user, not a separate `www-data`
that would need looser 775/777 permissions — confirm which applies for this
specific account rather than assuming). Confirm by loading any page after
`.env` is in place (step 5) — a permissions problem here surfaces
immediately as a 500 error, and the specific error will be a
`file_put_contents(): Failed to open stream: Permission denied` pointing at
`storage/logs/laravel.log` or `bootstrap/cache/*.php`.

---

## 5. `.env` — full production variable list

Create `.env` directly on the server (Plesk File Manager's editor, or
upload it as the one exception to "don't upload local files" in step 3 —
just never commit it to git). Start from `.env.example` in the repo root,
which already documents every variable; the values that **must** change
from their dev defaults are:

```env
APP_NAME=LCpumps
APP_ENV=production
APP_KEY=                          # generate in step 6, don't hand-write this
APP_DEBUG=false                   # never true in prod — leaks stack traces with file paths
APP_URL=https://www.lcpumps.kz    # exact — see the note below, this is not cosmetic

APP_LOCALE=ru
APP_FALLBACK_LOCALE=ru

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1                 # confirm in Plesk — usually correct for same-box MySQL
DB_PORT=3306
DB_DATABASE=                      # from Plesk → Databases, after creating one
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=10080            # 7 days — matches the source site's session length
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=database

MAIL_MAILER=smtp                  # NOT "log" — log silently drops every real email in prod
MAIL_SCHEME=null
MAIL_HOST=                        # Plesk mailbox SMTP host, step 7
MAIL_PORT=587
MAIL_USERNAME=                    # full mailbox address, step 7
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="no-reply@lcpumps.kz"
MAIL_FROM_NAME="${APP_NAME}"

CONTACT_NOTIFY_TO=info@lcpumps.kz # who receives contact-form submissions

UPLOADS_PUBLIC_DIR=uploads
UPLOADS_PRIVATE_DIR=private-uploads

TRUSTED_PROXIES=                  # fill in step 9 — do not skip, see that section

VITE_APP_NAME="${APP_NAME}"
```

**`APP_URL` is not cosmetic.** The sitemap, canonical URLs, and hreflang
alternates (`App\Services\SeoMetadataService`) are all built from
`config('app.url')`, not from the request's `Host` header. A wrong value
here produces a sitemap and `<link rel="alternate">` tags pointing at the
wrong domain, and Google will index the wrong canonical origin. The site
must answer on `www.lcpumps.kz` specifically — the bare `lcpumps.kz` domain
is a registrar-level redirect to `www`, not a second valid origin — so
`https://www.lcpumps.kz` (with `www`, with `https`) is the one correct
value.

---

## 6. Generate the app key, run migrations and seeders (no SSH)

Without SSH, `php artisan` commands run through Plesk's **Scheduled
Tasks** (Plesk → Domains → `<domain>` → Scheduled Tasks → add task → "Run a
PHP script", or the equivalent "run command" feature depending on the Plesk
version) — each one-off command becomes a task you add, run once manually,
then delete (don't leave one-off setup tasks in the recurring schedule).

Run these, **in this order** (each depends on the previous one):

1. `php artisan key:generate --force` — writes `APP_KEY` into `.env`
   directly on the server. Must happen before anything else touches
   sessions/encryption.
2. `php artisan migrate --force` — creates all tables (`users`, `sessions`,
   `cache`, `jobs`, `products`, `product_translations`, `sections`,
   `admin_users`, `contact_submissions`, `submission_attachments`, etc.).
   `--force` is required because `migrate` normally refuses to run with
   `APP_ENV=production` without it, as a safety prompt that has nowhere to
   prompt in a non-interactive Scheduled Task.
3. `php artisan db:seed --force` — runs `DatabaseSeeder` →
   `CatalogSeeder`, populating the product catalog. Without this the site
   comes up with empty category/product pages.
4. `php artisan storage:link` — symlinks `public/storage` →
   `storage/app/public`, needed for publicly-served product images
   (`UPLOADS_PUBLIC_DIR`) to resolve. Skip this for anything under
   `storage/app/private` — that's the private-attachments disk and must
   stay unlinked, see step 10.

After each step, check the task's output log in Plesk for errors before
moving to the next — a failed migration leaves the DB in a partial state
that the next command will fail against differently, which is harder to
debug than catching it at the source.

---

## 7. Create the admin account

Same Scheduled Task mechanism as step 6, run once:

```
php artisan admin:seed-user --username=<chosen username> --password=<chosen password>
```

The `--username`/`--password` flags are required here specifically because
the command's interactive prompts (built on `Laravel\Prompts`) need a real
TTY, which a Scheduled Task doesn't provide — running the command with no
flags in this context will hang or fail, not prompt. Password must be 8+
characters (enforced by the command itself). Delete the task after running
it once — don't leave admin credentials sitting in a Scheduled Task's
command history longer than necessary.

Verify: log in at `https://www.lcpumps.kz/admin/login` with the credentials
just created.

---

## 8. SMTP — Plesk mailbox, and verify delivery end to end

Create a mailbox in Plesk (Plesk → Mail → `<domain>` → Create Email
Address) — e.g. `no-reply@lcpumps.kz` — and get its SMTP credentials (Plesk
shows the SMTP host/port for its own mail service, typically the domain
itself on port 587 with STARTTLS, but confirm in the panel rather than
assuming). Fill into `.env` from step 5:

```env
MAIL_MAILER=smtp
MAIL_HOST=<Plesk SMTP host>
MAIL_PORT=587
MAIL_USERNAME=no-reply@lcpumps.kz
MAIL_PASSWORD=<mailbox password>
```

**Verify actual delivery, not just that the form doesn't error**: submit
the real contact form at `https://www.lcpumps.kz/contact` with a test
message, then confirm **both** of:

1. The submission appears in the admin panel (Requests list) — this
   confirms the DB write path works, independent of mail.
2. An email actually arrives at the `CONTACT_NOTIFY_TO` address — this
   confirms SMTP works. `ContactSubmissionHandler` is written so a failed
   notification email does **not** block the DB save (there's a test for
   this: `submission is saved even when notification email fails`), which
   means a broken SMTP config would silently produce zero emails while the
   admin panel still looks fine — the only way to catch that is checking
   the inbox, not just the panel.

If the email doesn't arrive, check `storage/logs/laravel.log` for the SMTP
error — common causes are the wrong port (465 needs `MAIL_SCHEME=smtps`
instead of STARTTLS on 587) or the mailbox password being wrong.

---

## 9. Upload/body-size limits

Required for the contact form's attachment uploads (15 MB/file, 40 MB
total — see `App\Services\ContactSubmissionHandler`). **Two separate layers
enforce size limits and both must be raised, or the lower one wins**:

### PHP (Plesk → Domains → `<domain>` → PHP Settings)

| Setting               | Value | Why                                                                  |
|------------------------|-------|-----------------------------------------------------------------------|
| `upload_max_filesize`  | `20M` | Margin above the app's 15 MB per-file cap                             |
| `post_max_size`        | `45M` | Margin above the app's 40 MB total-attachment cap, plus form fields    |
| `max_file_uploads`     | `20`  | PHP default; enough headroom for a handful of attachments              |

**Known PHP-level edge case:** if the whole POST body exceeds
`post_max_size`, PHP drops `$_POST`/`$_FILES` before Laravel (or any
session/CSRF middleware) ever runs — the app can't see the request at that
point. Laravel's `ValidatePostSize` middleware still catches this via the
`Content-Length` header and redirects with `?contactFormError=totalSizeExceeded`
(a bare error code, not text — see `bootstrap/app.php`'s
`PostTooLargeException` render callback and `ContactController::submit`'s
doc block). The margin above is what keeps *legitimate* 40 MB submissions
from ever hitting this path; it's the fallback if someone does anyway.

### nginx (separate from the PHP settings above)

Plesk's nginx sits in front of PHP-FPM and enforces its own request-body
size cap *before* the request ever reaches PHP — nginx's default is only
**1 MB**. Raising the PHP settings alone does nothing if this is left at
default: nginx returns its own 413 first, PHP never runs, and none of the
app's error handling (including the `PostTooLargeException` fallback
above, which is a *PHP*-level mechanism) ever fires — the visitor just
gets nginx's bare error page.

Set it to match `post_max_size` (Plesk → Domains → `<domain>` → Apache &
nginx Settings → "Additional nginx directives"):
```
client_max_body_size 45m;
```

**Verify both layers with real uploads, not just reading the config back**:
submit the contact form with an attachment just under 40 MB total (should
succeed) and one just over (should show the app's own "too large" message,
not an nginx error page or a blank timeout).

---

## 10. `TrustProxies` — rate limiter correctness

Plesk puts nginx in front of PHP-FPM. The app's rate limiters (contact
form, admin login) key on `$request->ip()`, which is only spoof-resistant
if Laravel is told which hop to trust — otherwise either:

- nothing is trusted (the default) → `$request->ip()` reports nginx's own
  IP for every visitor, and every visitor shares one rate-limit bucket
  (one abusive visitor locks out everyone else), or
- everything is blindly trusted → an attacker sets `X-Forwarded-For`
  themselves and gets a fresh bucket on every request, defeating the
  limiter entirely — this is the failure mode this step exists to prevent.

**Steps:**

1. In Plesk, confirm PHP-FPM and nginx run on the same box (typical for
   this kind of shared/VPS hosting) — that almost always means nginx
   proxies to `127.0.0.1` from PHP-FPM's point of view.
2. Confirm the actual peer PHP sees: temporarily dump
   `request()->server('REMOTE_ADDR')` from any route and load the page —
   that value is what belongs in `TRUSTED_PROXIES`.
3. Set in `.env`:
   ```
   TRUSTED_PROXIES=127.0.0.1
   ```
   (comma-separate if `REMOTE_ADDR` turns out to be something else, or use
   `*` only if you've confirmed PHP-FPM is never reachable except through
   that one proxy — see the reasoning in `config/trustedproxy.php`).
4. **Verify it actually worked, don't just assume**: hit the admin login 5
   times with a spoofed `X-Forwarded-For` header changing on every request
   (e.g. `curl -H "X-Forwarded-For: 1.2.3.4" ...`, incrementing the IP each
   call) and confirm the 6th attempt still gets rate-limited
   ("Слишком много попыток") — if it doesn't, `TRUSTED_PROXIES` is wrong
   (or nginx isn't appending to the header the way assumed) and the
   limiter is bypassable.

If config caching is used (`php artisan config:cache` — recommended for
Plesk, run it as one more Scheduled Task after `.env` is finalized),
re-run it after changing `TRUSTED_PROXIES`: it's read at boot from
`config/trustedproxy.php`, not re-evaluated from `.env` on every request.

---

## 11. Verify private attachments are not directly reachable

Contact-form attachments are stored on the `local` filesystem disk
(`storage/app/private/`, see `config/filesystems.php`), which is
deliberately **never** linked into `public/` — unlike product images
(`public` disk, `storage/app/public/`, symlinked via `storage:link` in step
6). They're only reachable through `AttachmentController::download()`,
which sits behind the `admin.auth` middleware.

**Verify this holds on the real deployment, not just in code**: after a
real contact-form submission with an attachment exists (step 8's test
submission works for this), try to guess/construct a direct URL to the
uploaded file under `https://www.lcpumps.kz/storage/private-uploads/...` —
this must 404. If it instead serves the file, something in steps 3–6
exposed more of `storage/` than intended (wrong document root from step 3
is the most likely cause — re-check that first).

---

## 12. DNS cutover — do this last, and only after everything above is verified

`www.lcpumps.kz` currently resolves via a **CNAME record pointing at
Railway** (the platform currently hosting the live Next.js site). Cutting
over means changing that CNAME to an **A record pointing at the PS.kz
server's IP** instead — CNAME and A records aren't interchangeable in a
DNS zone (a CNAME can't coexist with other records on the same name), so
this is a delete-and-recreate, not an edit.

**Order of operations, strictly:**

1. Do this **only after** steps 1–11 are all verified working — the new
   site should already be fully checked out and working at its own
   IP/hostname (e.g. via the server's raw IP with a `Host` header override,
   or a Plesk-assigned temporary subdomain) before DNS ever points visitors
   at it.
2. Lower the DNS TTL on the current CNAME record a day or so ahead of the
   cutover, if the registrar/DNS provider allows it — this shortens how
   long visitors keep resolving to the old (Railway) address after the
   record changes, reducing the window where behavior is inconsistent
   across visitors.
3. Delete the CNAME, create the A record pointing at the PS.kz server's IP.
4. Propagation can take anywhere from minutes to the old TTL's full
   duration. Monitor from a few different networks/DNS resolvers (not just
   your own machine, which may cache) until `https://www.lcpumps.kz`
   consistently resolves to the new IP and serves the new site.
5. **Decommission Railway last, only after DNS has fully propagated and the
   acceptance checklist below has been re-run against the live
   `www.lcpumps.kz` domain (not just the temporary IP/subdomain).** Until
   that's confirmed, Railway is the safety net — the client's site must
   keep working throughout, and Railway is what guarantees that if the
   cutover needs to be rolled back (DNS change reverted) before it's fully
   verified.

---

## Acceptance checklist — run after step 12, against the live `www.lcpumps.kz` domain

- [ ] Home page opens correctly on all 4 locales: `/`, `/kk`, `/en`, `/zh`
- [ ] Product catalog shows real products (not empty) — check `/products`
      and at least one category page (`/products/pumps`)
- [ ] Contact form submission arrives **both** in the admin panel and as an
      email to `CONTACT_NOTIFY_TO`
- [ ] Admin login works with the account created in step 7
- [ ] SSL certificate is valid (Plesk's Let's Encrypt integration, or
      whatever cert PS.kz provisions) — no browser warning on
      `https://www.lcpumps.kz`
- [ ] `/sitemap.xml` and `/robots.txt` both return successfully (not 404,
      not a PHP error page)
- [ ] `https://lcpumps.kz` (bare domain, no `www`) redirects to
      `https://www.lcpumps.kz`
