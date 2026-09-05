# Deploying TuxCMS to Spaceship shared hosting

One CMS install serves one business website. The Laravel app, the admin
dashboard and the generated static site all live under a single domain:

```
~/tuxcms/                     the Laravel app
  public/                     <- the document root
    index.html                generated homepage
    about/index.html          generated pages
    sitemap.xml  robots.txt  404.html
    admin/                    the dashboard SPA (built, not tracked in git)
    build/                    compiled site CSS (built, not tracked in git)
    storage -> ../storage/app/public   uploaded media
    index.php                 Laravel, reached only by /api and /up
```

`public/.htaccess` is what makes this work. Requests matching a real file or
directory are served straight off disk, so a visitor reading the site never
starts PHP. Only `/api/*` and `/up` reach Laravel. Anything else returns a real
404 that renders the generated `404.html`.

---

## What you need from Spaceship

| Requirement | Why |
|---|---|
| PHP 8.2+ | Laravel 12 |
| MySQL database | content storage |
| SSH access | running `composer`, `artisan`, and creating the media symlink |
| Cron | scheduled publishing |
| Ability to set the domain's document root | pointing the domain at `public/` |

Check the SSH and document-root items before you start; they decide which of
the layouts in step 3 you use. If your plan has no SSH, see "Without SSH" at
the end.

---

## 1. Build everything locally

Shared hosting has no Node, so both front-end builds happen on your machine.

```bash
npm ci && npm run build          # site CSS -> public/build
cd dashboard && npm ci && npm run build && cd ..   # dashboard -> public/admin
```

Then remove development dependencies from the PHP side:

```bash
composer install --no-dev --optimize-autoloader
```

## 2. Upload

Upload the whole project directory to `~/tuxcms` (SFTP, or cPanel's File
Manager with a zip). Include `vendor/`, `public/build/` and `public/admin/` —
they are gitignored but they are exactly what the server cannot build itself.

Leave out `node_modules/`, `dashboard/`, `tests/` and `.git/`.

Do not upload your local `.env`.

## 3. Point the domain at `public/`

The app directory must not be web-accessible; only `public/` is. Pick whichever
your plan supports, in this order:

**a. Set the document root (preferred).** In cPanel → Domains, set the domain's
document root to `tuxcms/public`.

**b. Symlink.** Over SSH:

```bash
rm -rf ~/public_html && ln -s ~/tuxcms/public ~/public_html
```

**c. Neither is available.** Leave the app at `~/tuxcms`, move the *contents* of
`tuxcms/public` into `~/public_html`, and edit the two `require` paths in
`~/public_html/index.php` to point at `../tuxcms/`. Then set
`SITE_OUTPUT_PATH=/home/USER/public_html` in `.env` so publishing writes to the
right place. This works but leaves you maintaining two directories; prefer (a).

## 4. Create the database

cPanel → MySQL Databases. Create a database and a user, and grant that user all
privileges on it. Note the *prefixed* names cPanel generates
(`cpaneluser_tuxcms`), not what you typed.

## 5. Configure `.env`

Copy `.env.example` to `.env` on the server and set:

```ini
APP_NAME="Your Business"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
SITE_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=cpaneluser_tuxcms
DB_USERNAME=cpaneluser_tuxcms
DB_PASSWORD=the-password-you-set

FILESYSTEM_DISK=public
```

**`APP_ENV=production` is not optional.** Outside production every generated
page ships `<meta name="robots" content="noindex, nofollow">` and `robots.txt`
disallows everything — a deliberate guard so staging builds never get indexed.
Leave it as `local` and your live site will never appear in Google.

`SITE_URL` is equally load-bearing: the build runs from the CLI where there is
no request host, so canonical tags, Open Graph URLs, JSON-LD and the sitemap all
read it. Getting it wrong points every canonical at the wrong domain.

Then generate a key and cache the config:

```bash
cd ~/tuxcms
php artisan key:generate
php artisan config:cache && php artisan route:cache
```

Re-run `config:cache` after any later `.env` edit, or the change is ignored.

## 6. Migrate, link media, create your login

```bash
php artisan migrate --force
php artisan storage:link          # media is served from public/storage
php artisan user:create           # prompts for name, email, password
```

If `storage:link` is blocked, copy `storage/app/public` to `public/storage`
instead and re-copy after uploads — but check first, most cPanel hosts allow it.

## 7. Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

## 8. Publish the site

```bash
php artisan site:build
```

This writes the HTML into `public/`. Load `https://yourdomain.com` — you should
see the generated homepage, not Laravel.

## 9. Cron for scheduled publishing

A static site has no request to trigger anything, so a page whose `published_at`
matures in the future would never go live without this. In cPanel → Cron Jobs,
every minute:

```
* * * * * cd /home/USER/tuxcms && php artisan schedule:run >> /dev/null 2>&1
```

Laravel's scheduler decides what actually runs (a build every 15 minutes, plus a
nightly one at 03:30). If Spaceship enforces a minimum interval, use their
smallest — the schedule still works, it just checks less often.

---

## Verify

```bash
curl -sI https://yourdomain.com/                    # 200
curl -s  https://yourdomain.com/ | grep -c 'name="robots"'   # 0 in production
curl -sI https://yourdomain.com/nope                # 404, themed page
curl -sI https://yourdomain.com/admin/              # 200
curl -s  https://yourdomain.com/robots.txt          # Allow, plus sitemap URL
curl -s  https://yourdomain.com/sitemap.xml | head
```

Then log in at `https://yourdomain.com/admin/`, edit a page, hit **Publish**, and
confirm the change appears on the public URL.

---

## Updating later

Content changes need no deployment — edit and hit Publish.

For code changes: rebuild locally (step 1), upload the changed files, then

```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache
php artisan site:build
```

`site:build` only ever removes files recorded in its own manifest, so
`admin/`, `build/`, `storage/`, `index.php` and `.htaccess` are never at risk.

---

## Without SSH

Everything above except `composer`, `artisan` and `ln` can be done through
cPanel. To work around the rest:

- **Composer**: run `composer install --no-dev --optimize-autoloader` locally and
  upload `vendor/`.
- **`key:generate`**: generate a key locally with `php artisan key:generate
  --show` and paste it into the server's `.env` as `APP_KEY`.
- **`migrate`**: export your local schema with `mysqldump --no-data` and import
  it through phpMyAdmin. Import the `migrations` table's rows too, or a later
  `migrate` will try to re-run everything.
- **`storage:link`**: use layout (c) above and copy `storage/app/public` into
  `public_html/storage`.
- **`user:create`**: create the row by hand in phpMyAdmin with a bcrypt hash.
- **`site:build`**: this one you cannot avoid. Add it as a cron job set to run
  once, or use cPanel's "Terminal" if the plan includes it.

Without a shell, publishing depends entirely on cron, so confirm cron works
before relying on it.

---

## Reserved page slugs

`admin`, `api`, `build`, `storage` and `up` are real directories in the docroot,
so the CMS refuses them as top-level page slugs — a page at `/admin` would
otherwise overwrite the dashboard on the next publish. Nested pages are fine:
`/about/admin` is just a subdirectory. The list lives in `config/site.php`.
