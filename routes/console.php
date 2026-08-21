<?php

use Illuminate\Support\Facades\Schedule;

/*
 * Scheduled publishing.
 *
 * On a static site nothing happens when a page's `published_at` matures —
 * there is no request to trigger it. Without this, scheduled content would
 * simply never go live. The build is cheap (well under a second for a small
 * site) and writes files atomically, so running it regularly is safe.
 *
 * Requires one cron entry on the server:
 *   * * * * * cd /path/to/tuxcms && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('site:build --quiet-progress')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// A nightly rebuild is a safety net: it picks up anything the periodic run
// missed and keeps `lastmod` in the sitemap honest.
Schedule::command('site:build --quiet-progress')
    ->dailyAt('03:30')
    ->withoutOverlapping();
