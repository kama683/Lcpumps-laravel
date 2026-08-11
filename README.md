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
