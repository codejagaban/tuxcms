<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class PublishState
{
    private const CHANGED_KEY = 'content_changed_at';

    private const PUBLISHED_KEY = 'last_published_at';

    public static function markChanged(): void
    {
        Setting::set(self::CHANGED_KEY, now()->toAtomString(), 'system');
    }

    public static function markPublished(Carbon $builtFrom): void
    {
        Setting::set(self::PUBLISHED_KEY, $builtFrom->toAtomString(), 'system');
    }

    public static function lastPublishedAt(): ?Carbon
    {
        $value = Setting::getString(self::PUBLISHED_KEY);

        return $value ? Carbon::parse($value) : null;
    }

    public static function hasPendingChanges(): bool
    {
        $changed = Setting::getString(self::CHANGED_KEY);
        $published = self::lastPublishedAt();

        return ! $published || ($changed && Carbon::parse($changed)->greaterThan($published));
    }

    public static function pendingCount(): int
    {
        if (! self::lastPublishedAt()) {
            return max(Page::withTrashed()->count(), self::hasPendingChanges() ? 1 : 0);
        }

        return self::hasPendingChanges() ? 1 : 0;
    }
}
