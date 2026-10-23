# LCpumps — Laravel/MySQL migration

PHP 8.3 / Laravel 13 / MySQL port of the Next.js site in `../site/`, targeting
PS.kz shared hosting (Plesk, no SSH, no Node.js at runtime). `../site/` stays
untouched as the visual/data reference and stays live on Railway until this
version is ready. See `DEPLOY.md` for production deployment.

## Local development environment

This project is developed inside **WSL2 (Ubuntu)**, not native Windows — the
Windows host has no PHP/Composer/MySQL. All `php`, `composer`, and `artisan`
commands below are meant to be run from an Ubuntu WSL shell, e.g.:

```bash
wsl -d Ubuntu
cd "/mnt/c/Users/<you>/Desktop/bellery pumps/laravel"
```

**MySQL does not auto-start after a Windows reboot.** If artisan commands
start failing with a "could not find driver" or connection-refused error,
run:

```bash
sudo service mysql start
```

### First-time setup

```bash
composer install
cp .env.example .env   # already done in this repo's working copy; .env is gitignored
php artisan key:generate
php artisan migrate
php artisan db:seed              # loads the product/section catalog (CatalogSeeder)
php artisan admin:seed-user --username=admin --password=<your-password>
php artisan serve
```

Local DB credentials (see `.env`): database `lcpumps`, user `lcpumps` /
`lcpumps_dev`.

### Admin account

There is a single admin account, in the `admin_users` table, created/updated
via:

```bash
php artisan admin:seed-user --username=admin --password=<min 8 chars>
```

Omit `--username`/`--password` to be prompted interactively instead.

## Deploying to another server

Everything needed to build and run the site is in this repository except
secrets and data (see "Handed over separately" below). Requirements: PHP 8.3
with the extensions Laravel needs, Composer, MySQL/MariaDB, Apache or nginx
with the document root set to `public/`.

```bash
git clone https://github.com/kama683/Lcpumps-laravel.git && cd Lcpumps-laravel
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate        # or paste the production APP_KEY
# edit .env: APP_URL, APP_ENV=production, APP_DEBUG=false, DB_*, MAIL_*,
# CONTACT_NOTIFY_TO, TRUSTED_PROXIES (see comments in .env.example)
php artisan migrate --force     # empty DB; or import the DB dump instead
php artisan db:seed --force     # only for an empty DB: loads the catalog
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Make `storage/` and `bootstrap/cache/` writable by the web server user.

### Frontend build

`public/build/` (including `manifest.json`) is **committed**, so the server
needs no Node.js. To rebuild locally: `npm ci && npm run build`, then commit
`public/build/` as a whole.

A fresh build reproduces the JS chunks byte-for-byte, but the CSS hash
differs from the committed `app-CPMKctuZ.css`. Cause: `resources/css/app.css`
has `@source '../../storage/framework/views/*.php'`, and the committed CSS was
built next to a populated, git-ignored compiled-view cache (plus
`vendor/`), which contributes ~65 extra utility classes from Laravel's own
views. None of them is used by the site's templates, so a clean-clone CSS is
functionally equivalent but not hash-identical. The committed build is the
canonical one for production.

### Handed over separately (never in git)

- Production `.env` (incl. `APP_KEY`, DB and SMTP credentials)
- MySQL dump: `sections`, `section_translations`, `products`,
  `product_translations`, `admin_users`, `contact_submissions`,
  `submission_attachments`
- `storage/app/public` and `storage/app/private` (uploaded files)

See `DEPLOY.md` for the PS.kz (Plesk) specifics.

<!-- test autodeploy -->
