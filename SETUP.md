# Setting up TuxCMS locally

From a fresh clone to a published page. For a production server, see
[DEPLOYMENT.md](DEPLOYMENT.md).

## 1. Install

You need PHP 8.3 or newer, Composer and Node 20 or newer.

```bash
composer install
npm install && npm --prefix dashboard install
cp .env.example .env && php artisan key:generate
```

## 2. Configure

Open `.env` and check three things.

**The database.** `.env.example` is set up for MySQL. For local work SQLite is
less to run:

```
DB_CONNECTION=sqlite
```

with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`
removed, and an empty file at `database/database.sqlite`.

**The two URLs.** `APP_URL` is where the API answers
(`http://localhost:8000`). `SITE_URL` is where the generated site is read
(`http://localhost:8080`); it goes into canonical tags, the sitemap and every
absolute link the build writes.

**The theme.** `SITE_THEME` must name a directory under
`resources/views/site/themes`. Crystal is the theme in the repository, so use:

```
SITE_THEME=crystal
```

Mail is set to `log` by default, so enquiries and applications are written to
`storage/logs/laravel.log` rather than sent.

## 3. Create the database

```bash
php artisan migrate --seed
php artisan storage:link
```

The seed adds the default settings, the roles and a starter admin account
defined in `database/seeders/DatabaseSeeder.php`. That account has a published
password, so it is for local use only: make your own editor and do not run
`--seed` on a server.

```bash
php artisan user:create
```

For a site to look at straight away, load the Crystal content and its
photographs:

```bash
php artisan db:seed --class=CrystalContentSeeder
```

```bash
php artisan crystal:import-media
```

## 4. Run it

Three processes, one per part:

```bash
php artisan serve --port=8000
```

```bash
npm --prefix dashboard run dev
```

```bash
php -S 127.0.0.1:8080 -t public public/dev-router.php
```

Sign in to the dashboard at `http://localhost:5173`.

The site's own stylesheet is compiled by the root Vite config. Run
`npm run build` once, or `npm run dev` while changing site styles.

## 5. Publish

Nothing appears on `:8080` until the site is built. Press Publish in the
dashboard, or:

```bash
php artisan site:build
```

The build writes the pages into `public/` and removes any it wrote before that
no longer exist. Those files are ignored by git.

## 6. Check your work

```bash
php artisan test
```

```bash
npm --prefix dashboard run lint
```

```bash
npm --prefix dashboard run test:e2e
```

The Playwright specs start their own dashboard server and mock the API, so they
need neither the database nor the three processes above. They do need Chrome
installed.

## When something is off

**The site on `:8080` is a 404.** It has not been built yet. Run
`php artisan site:build`.

**A page saves but the site does not change.** Saving does not publish. The
Publish button shows the number of pending changes.

**Uploaded images do not load.** `php artisan storage:link` has not been run,
or `FILESYSTEM_DISK` is not `public`.

**A scheduled page never goes live.** Scheduled publishing depends on cron
calling `php artisan schedule:run`. Locally, run `php artisan schedule:work` or
build by hand.

**A slug is refused.** `admin`, `api`, `build`, `storage` and `up` are real
directories in `public/` and cannot be used for a top-level page.

**The dashboard cannot reach the API.** Check the API is on port 8000 and that
`dashboard/vite.config.js` proxies to it.
