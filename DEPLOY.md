# Deploy notes

Full deployment runbook is Stage 10. This file currently only holds the
PHP settings the contact form (Stage 6) requires — added early because they
need to be set in Plesk regardless of when the rest of this doc is written.

## APP_URL and canonical host (required)

Before any deploy, verify `APP_URL` in `.env` and the live Plesk app config is
set to `https://www.lcpumps.kz` exactly, not the old `lcpumps.com` or a bare
root-domain variant.

This is not cosmetic: the sitemap, canonical URLs, and hreflang alternates are
all built from `config('app.url')`, not from the request's `Host` header. An
incorrect value produces a sitemap and canonical tags on the wrong domain, and
Google will treat the live URLs as redirecting to the wrong origin. The public
site answers only on `www`, while the bare domain is a 302 redirect from the
registrar, so `https://www.lcpumps.kz` is the correct canonical host.

Also verify the mail addresses in `.env` match the live domain:

```env
APP_URL=https://www.lcpumps.kz
MAIL_FROM_ADDRESS="no-reply@lcpumps.kz"
CONTACT_NOTIFY_TO=info@lcpumps.kz
```

## PHP settings (Plesk → Domains → \<domain\> → PHP Settings)

Required for the contact form's attachment upload limits (15 MB/file, 40 MB
total — see `App\Services\ContactSubmissionHandler`):

| Setting              | Value | Why                                                                 |
|-----------------------|-------|----------------------------------------------------------------------|
| `upload_max_filesize`  | `20M` | Margin above the app's 15 MB per-file cap                            |
| `post_max_size`        | `45M` | Margin above the app's 40 MB total-attachment cap, plus form fields   |
| `max_file_uploads`     | `20`  | PHP default; enough headroom for a handful of attachments             |

**Known PHP-level edge case:** if the whole POST body exceeds `post_max_size`,
PHP drops `$_POST`/`$_FILES` before Laravel (or any session/CSRF middleware)
ever runs — the app can't see the request at that point. Laravel's
`ValidatePostSize` middleware still catches this via the `Content-Length`
header and the app redirects with `?contactFormError=totalSizeExceeded` (a
bare error code, not text — see `bootstrap/app.php`'s `PostTooLargeException`
render callback and `ContactController::submit`'s doc block) — the margin
above is what keeps *legitimate* 40 MB submissions from ever hitting this
path; it's the fallback if someone does anyway.

### nginx: `client_max_body_size` — separate from the PHP settings above

Plesk's nginx sits in front of PHP-FPM and enforces its own request-body
size cap *before* the request ever reaches PHP — nginx's default is only
**1 MB**. Raising `upload_max_filesize`/`post_max_size` alone does nothing
if this is left at the default: nginx returns its own 413 first, PHP never
runs, and none of the app's error handling (including the
`PostTooLargeException` fallback above, which is a *PHP*-level mechanism)
ever gets a chance to fire — the visitor just gets nginx's bare error page.

Set it to match `post_max_size` (Plesk → Domains → \<domain\> → Apache &
nginx Settings → "Additional nginx directives"):
```
client_max_body_size 45m;
```
Verify with the same curl-based check as the `post_max_size` margin above —
a request just under 45 MB should reach the app (and get the app's own
`totalSizeExceeded` handling if it's actually over the app's 40 MB cap), not
an nginx error page.

## TrustProxies (Stage 7)

Plesk puts nginx in front of PHP-FPM. The app's rate limiters (contact form,
Stage 6; admin login, Stage 7) key on `$request->ip()`, which is only
spoof-resistant if Laravel is told which hop to trust — otherwise either:

- nothing is trusted (today's default) → `$request->ip()` reports nginx's
  own IP for every visitor, not the real client, and every visitor shares
  one rate-limit bucket, or
- everything is blindly trusted → an attacker sets `X-Forwarded-For`
  themselves and gets a fresh bucket on every request, defeating the limiter
  entirely (this is the bug this section exists to prevent).

**What to set once hosting is provisioned:**

1. In Plesk, check whether PHP-FPM and nginx run on the same box (typical
   for shared/VPS Plesk hosting) or are split across hosts. Same box almost
   always means nginx proxies to `127.0.0.1` from PHP-FPM's point of view.
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
4. **Verify it actually worked**, don't just assume: submit the contact form
   (or hit the admin login) 5 times with a spoofed `X-Forwarded-For` header
   changing on every request (e.g. via `curl -H "X-Forwarded-For: 1.2.3.4"`,
   incrementing the IP each call) and confirm the 6th attempt still gets
   `rateLimited`/"Слишком много попыток" — if it doesn't, `TRUSTED_PROXIES`
   is wrong (or nginx isn't appending to the header the way assumed) and the
   limiter is bypassable.

If `php artisan config:cache` is used in deployment (recommended for Plesk),
re-run it after changing `TRUSTED_PROXIES` — it's read at boot from
`config/trustedproxy.php`, not re-evaluated from `.env` on every request.

**Creating the admin account:** no SSH on this tariff, so `php artisan
admin:seed-user` needs to run via a one-off Plesk Scheduled Task (or the
"Run PHP script"/artisan console feature if the Plesk plan has one) rather
than an interactive terminal — pass `--username=` and `--password=` flags
since the command's interactive prompts (`Laravel\Prompts`) need a TTY that
a cron-style task won't have.

## Platform version check — **confirmed**, no longer a blocker

This app requires **PHP 8.3+** (Laravel 13 minimum) and uses PHP 8.3 syntax
(typed class constants, e.g. `private const array X = [...]`).

**Status:** PS.kz supports PHP 5.6–8.3 in their standard Plesk control panel
(confirmed via product page). Requirement is satisfied.

**After purchase, in Plesk → Domains → \<domain\> → PHP Settings:**

1. Verify the version dropdown actually shows 8.3 as available and is selected
2. Confirm the following extensions are enabled (all are standard, but Plesk
   can disable them per-domain):
   - `intl` (localization; used by Laravel Translatable)
   - `gd` (image operations; used by product-image upload)
   - `bcmath` (arbitrary-precision math; sometimes disabled on shared hosting)
   - `mbstring` (multibyte strings; standard, rarely disabled)
   - `zip` (archive extraction; used by some dependencies)
   - `pdo_mysql` (database driver; absolutely required)
   - `fileinfo` (MIME-type detection; used by attachment-upload validation)
