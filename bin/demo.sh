#!/usr/bin/env bash
#
# Try TuxCMS with the Shopfront theme on throwaway data.
#
# Everything runs on one port, the way production does: the generated site at
# /, the dashboard at /admin and the API at /api. It uses its own SQLite
# database and its own output directory, so the real content in .env's
# database and the pages already built into public/ are left alone.
#
#   bin/demo.sh            start it (first run seeds and builds)
#   bin/demo.sh --fresh    wipe the demo data and start again
#
set -euo pipefail
cd "$(dirname "$0")/.."

PORT="${DEMO_PORT:-8090}"
export APP_ENV=local
export APP_URL="http://127.0.0.1:${PORT}"
export SITE_URL="http://127.0.0.1:${PORT}"
export SITE_THEME=shopfront
export SITE_OUTPUT_PATH="$PWD/storage/app/demo-site"
export DB_CONNECTION=sqlite
export DB_DATABASE="$PWD/database/demo.sqlite"
export MAIL_MAILER=log
export SESSION_DRIVER=file
export CACHE_STORE=file
export QUEUE_CONNECTION=sync

if [[ "${1:-}" == "--fresh" ]]; then
    rm -f "$DB_DATABASE"
    rm -rf "$SITE_OUTPUT_PATH"
fi

if [[ ! -f public/admin/index.html ]]; then
    echo "Building the dashboard..."
    npm --prefix dashboard run build
fi

if [[ ! -s "$DB_DATABASE" ]]; then
    echo "Seeding the demo database..."
    touch "$DB_DATABASE"
    php artisan migrate --seed --force
    php artisan db:seed --class=DemoContentSeeder --force
fi

php artisan site:build --quiet-progress

echo
echo "  Site       http://127.0.0.1:${PORT}/"
echo "  Dashboard  http://127.0.0.1:${PORT}/admin"
echo "  Sign in with the starter account in database/seeders/DatabaseSeeder.php"
echo

exec php -S "127.0.0.1:${PORT}" -t public public/dev-router.php
