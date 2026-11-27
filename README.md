# TuxCMS

A content management system for one business website. Editors work in a React
dashboard, Laravel stores the content, and the site itself is published as plain
static HTML that visitors read without ever starting PHP.

## How it fits together

Three parts share a single domain:

| Part | Source | Serves |
| --- | --- | --- |
| Laravel API | `app/`, `routes/api.php` | `/api/v1/*` and `/up` |
| Dashboard (React 19, Vite) | `dashboard/` | `/admin`, built into `public/admin` |
| Generated site | `resources/views/site/` | every other path, as files under `public/` |

`public/.htaccess` serves any request that matches a real file or directory
straight off disk, so only `/api/*` and `/up` ever reach Laravel. Publishing
runs `php artisan site:build`, which writes `index.html`, `about/index.html` and
so on, alongside `sitemap.xml`, `robots.txt`, `llms.txt` and `404.html`.

Pages are built from ordered sections (hero, features, stats, testimonials,
team, FAQ, call to action, contact, contact form, map, free text). A page whose
template is an installed theme is edited directly on the real rendered site in
the dashboard's live preview.

## Requirements

PHP 8.3 or newer, Composer, Node 20 or newer, and MySQL. SQLite is fine for
local work and is what the test suite uses.

## Getting started

```bash
composer install
npm install && npm --prefix dashboard install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan user:create
```

Three processes serve the three parts in development:

```bash
php artisan serve --port=8000
```

```bash
npm --prefix dashboard run dev
```

```bash
php -S 127.0.0.1:8080 -t public public/dev-router.php
```

The API answers on `:8000`, the dashboard on `:5173`, and the generated site on
`:8080`. `.claude/launch.json` holds the same three as named configurations.

## Publishing

```bash
php artisan site:build
```

The dashboard's Publish button calls the same build through
`POST /api/v1/site/publish`. Scheduled pages need cron, because nothing on a
static site notices that a `published_at` has matured:

```bash
* * * * * cd /path/to/tuxcms && php artisan schedule:run >> /dev/null 2>&1
```

That runs a build every fifteen minutes, a nightly rebuild at 03:30, and prunes
held spam at 03:10.

## Themes

A theme is a directory of Blade views under
`resources/views/site/themes/<name>` with its static assets in
`public/themes/<name>`. Set `SITE_THEME` to choose one. Crystal is the theme in
the repository. `php artisan media:import-theme` pulls the photographs a theme's
pages use into the media library.

## Tests

```bash
php artisan test
```

```bash
npm --prefix dashboard run lint && npm --prefix dashboard run test:e2e
```

The PHP suite covers publishing, page revisions, reserved slugs, SEO output,
navigation, spam filtering and media import. The dashboard's Playwright specs
cover inline editing, image replacement, autosave and accessibility.

## Documentation

- [SETUP.md](SETUP.md) takes a fresh clone to a published page.
- [DEPLOYMENT.md](DEPLOYMENT.md) puts the whole thing on Spaceship shared hosting.
- [ARCHITECTURE.md](ARCHITECTURE.md) explains how it works and why.
- [API_DOCUMENTATION.md](API_DOCUMENTATION.md) is the endpoint reference.
- [INDEX.md](INDEX.md) maps the code: models, services, endpoints and commands.
- [TASKS.md](TASKS.md) tracks what is being worked on.
- [design.md](design.md) and [tokens.css](tokens.css) hold the dashboard's visual language.
