<?php

namespace App\Support;

use App\Models\Enquiry;

/**
 * The kinds of message the inbox takes, which a site's forms send as their
 * hidden `type`. Every site has `contact`, the plain contact form; anything
 * else is up to the site, declared in the theme.json of a theme it uses:
 *
 *     "enquiries": {
 *       "client_review": { "label": "Client review", "plural": "Client reviews" }
 *     }
 *
 * A theme may also give `contact` a label of its own. The core names no
 * one's forms: the inbox's tabs, the email subjects and the notification
 * addresses in Settings all follow this list.
 */
class EnquiryKinds
{
    private const CONTACT = ['contact' => ['label' => 'Enquiry', 'plural' => 'Enquiries']];

    /**
     * The kinds the site's forms may send: `contact`, then those of every
     * theme the site uses, its site theme and any theme its pages are drawn
     * with. A theme installed but unused adds nothing.
     *
     * @return array<string, array{label: string, plural: string}>
     */
    public static function declared(): array
    {
        $kinds = self::CONTACT;

        foreach (Site::themesInUse() as $theme) {
            $kinds = array_replace($kinds, ThemeManifest::enquiries($theme));
        }

        return $kinds;
    }

    /**
     * The kinds to show in the inbox: those declared, then any older ones
     * still holding messages, so nothing already received is left without a
     * tab when a theme changes.
     *
     * @return array<string, array{label: string, plural: string}>
     */
    public static function shown(): array
    {
        $kinds = self::declared();

        foreach (Enquiry::query()->distinct()->pluck('type') as $type) {
            $kinds[$type] ??= ['label' => self::readable($type), 'plural' => self::readable($type)];
        }

        return $kinds;
    }

    /** What one message of this kind is called, in the inbox and in emails. */
    public static function label(string $type): string
    {
        return self::declared()[$type]['label'] ?? self::readable($type);
    }

    private static function readable(string $type): string
    {
        return ucfirst(str_replace('_', ' ', $type));
    }
}
