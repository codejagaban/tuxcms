<?php

namespace App\Support;

/**
 * Decides whether a form submission looks like junk. Nothing is rejected:
 * a submission judged to be spam is still saved, so a wrong call can be
 * recovered from the dashboard. It is simply not emailed to the business.
 */
class SpamFilter
{
    /** A person cannot read and fill in a form faster than this. */
    private const MIN_SECONDS = 3;

    /** One of these alone marks a message as a sales pitch. */
    private const STRONG = [
        'rank higher', 'ranking on google', 'rank on google', 'first page of google', 'top of google',
        'search engine optim', 'seo ', 'seo.', 'seo,', 'backlink', 'link building', 'guest post',
        'web design service', 'website redesign', 'redesign your website', 'web development service',
        'ai-powered search', 'chatgpt, gemini', 'increase your traffic', 'more traffic to your',
        'digital marketing agency', 'lead generation service', 'virtual assistant service',
        'crypto', 'bitcoin', 'casino', 'forex', 'viagra', 'loan offer', 'business loan',
        'squarespace', 'shopify', 'wordpress', 'fiverr', 'upwork',
    ];

    /** Two or more of these together do the same. */
    private const WEAK = [
        'your website', 'we help businesses', 'potential in your',
        'i was going through your', 'i came across your', 'our agency',
        'free quote', 'free audit', 'no obligation', 'affordable price', 'best price',
        'boost your', 'grow your', 'presence on', 'online presence',
        'reply to this', 'unsubscribe',
    ];

    /** A page left open longer than this has to be reloaded. */
    private const MAX_SECONDS = 7 * 24 * 3600;

    /**
     * A signed note of when a form page was opened. The site script fetches
     * one as the page loads and sends it back with the form, so the server
     * knows the page was really opened in a browser, and how long ago,
     * without trusting a number the sender could simply make up.
     */
    public static function issueToken(?int $at = null): string
    {
        $at ??= time();

        return $at.'.'.self::sign($at);
    }

    /** Seconds since the token was issued, or null when it is not ours. */
    public static function tokenAge(mixed $token): ?int
    {
        if (! is_string($token) || ! preg_match('/^(\d{1,12})\.([a-f0-9]{64})$/', $token, $parts)) {
            return null;
        }

        if (! hash_equals(self::sign((int) $parts[1]), $parts[2])) {
            return null;
        }

        return time() - (int) $parts[1];
    }

    private static function sign(int $at): string
    {
        return hash_hmac('sha256', 'form-token:'.$at, (string) config('app.key'));
    }

    /**
     * @param  array<int, string|null>  $texts  the free-text parts of the submission
     * @param  mixed  $token  the signed token the site script sent back
     */
    public static function isSpam(array $texts, mixed $token): bool
    {
        // No valid token means something posted straight to the address
        // without opening the page. A very young one means no person could
        // have filled the form in yet.
        $age = self::tokenAge($token);

        if ($age === null || $age < self::MIN_SECONDS || $age > self::MAX_SECONDS) {
            return true;
        }

        $text = ' '.mb_strtolower(implode(' ', array_filter($texts))).' ';

        if ($text === '  ') {
            return false;
        }

        // This business works in English. A message written mostly in another
        // script (Cyrillic, Chinese, Arabic and so on) is a mass mailing.
        $letters = preg_match_all('/\p{L}/u', $text);
        $latin = preg_match_all('/\p{Latin}/u', $text);

        if ($letters >= 20 && $latin / $letters < 0.6) {
            return true;
        }

        foreach (self::STRONG as $phrase) {
            if (str_contains($text, $phrase)) {
                return true;
            }
        }

        $weak = 0;
        foreach (self::WEAK as $phrase) {
            if (str_contains($text, $phrase)) {
                $weak++;
            }
        }

        // Real enquiries rarely carry links; pitches usually do.
        $links = preg_match_all('#https?://|www\.#', $text);

        return $weak >= 2 || $links >= 2 || ($weak >= 1 && $links >= 1);
    }
}
