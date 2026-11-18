<?php

namespace App\Support;

/**
 * Decides whether a form submission is an unsolicited sales pitch or other
 * junk. It judges intent, not single words: a message is only held when it
 * both names something being sold and shows the sender is selling it.
 *
 * Nothing is rejected. A held submission is still saved with the reason, so
 * a wrong call can be seen and recovered from the dashboard; it is simply not
 * emailed to the business. An uncertain one is emailed as normal and flagged.
 */
class SpamFilter
{
    public const OK = 'ok';

    public const REVIEW = 'review';

    public const SPAM = 'spam';

    /** A person cannot read and fill in a form faster than this. */
    private const MIN_SECONDS = 3;

    /** A page left open longer than this has to be reloaded. */
    private const MAX_SECONDS = 7 * 24 * 3600;

    /** Never part of a genuine enquiry to a care staffing agency. */
    private const JUNK = [
        'crypto', 'bitcoin', 'casino', 'forex', 'viagra', 'backlink', 'backlinks',
        'link building', 'guest post', 'guest posting', 'payday loan',
    ];

    /**
     * What unsolicited pitches sell. Naming one of these is never enough on
     * its own: a real customer may mention a website or an app.
     */
    private const TOPICS = [
        'seo', 'search engine optimisation', 'search engine optimization', 'search ranking',
        'rank higher', 'ranking on google', 'rank on google', 'page on google', 'page of google', 'top of google',
        'ai-powered search', 'web design', 'website design', 'web designing', 'website designing',
        'web development', 'website development', 'website redesign', 'redesign', 'website maintenance',
        'custom website', 'custom websites', 'new website', 'mobile app', 'mobile apps', 'ios app', 'android app',
        'app development', 'software development', 'business software', 'digital marketing',
        'social media marketing', 'lead generation', 'google ads', 'virtual assistant',
        'wordpress', 'shopify', 'squarespace', 'wix',
    ];

    /** Clear signs the sender is offering their own services. */
    private const SELLING = [
        'we help', 'we offer', 'we provide', 'we specialise', 'we specialize', 'we build', 'we develop',
        'we design', 'we create', 'i can help', 'we can help',
        'our agency', 'our company', 'our firm', 'our services', 'our team of',
        'affordable', 'price list', 'package', 'packages', 'portfolio', 'portfolios', 'demo', 'demos',
        'free audit', 'free quote', 'free consultation', 'no obligation', 'potential in your',
        'skilled across', 'years of experience in',
    ];

    /** Weaker signs, which only count alongside the above. */
    private const NUDGES = [
        'we are a', 'we are an', 'i am a', 'i am an', 'your website', 'your site', 'your business', 'i was going through your', 'i came across your',
        'pricing', 'discuss your requirement', 'discuss your requirements', 'grow your', 'boost your',
        'increase your', 'more traffic', 'more customers', 'online presence', 'are you interested',
        'if you are interested', 'reply to this', 'let me know if', 'unsubscribe',
    ];

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
     * @return array{verdict: string, reason: ?string}
     */
    public static function judge(array $texts, mixed $token): array
    {
        // No valid token means something posted straight to the address
        // without opening the page. A very young one means no person could
        // have filled the form in yet.
        $age = self::tokenAge($token);

        if ($age === null) {
            return self::result(self::SPAM, 'Sent without opening the form page');
        }

        if ($age < self::MIN_SECONDS) {
            return self::result(self::SPAM, 'Sent faster than a person could fill in the form');
        }

        $text = mb_strtolower(implode(' ', array_filter($texts)));

        // This business works in English. A message written mostly in another
        // script (Cyrillic, Chinese, Arabic and so on) is a mass mailing.
        $letters = preg_match_all('/\p{L}/u', $text);
        $latin = preg_match_all('/\p{Latin}/u', $text);

        if ($letters >= 20 && $latin / $letters < 0.6) {
            return self::result(self::SPAM, 'Not written in English');
        }

        if ($junk = self::found(self::JUNK, $text)) {
            return self::result(self::SPAM, 'Junk topic: '.$junk[0]);
        }

        $topics = self::found(self::TOPICS, $text);
        $selling = self::found(self::SELLING, $text);
        $nudges = self::found(self::NUDGES, $text);
        $links = preg_match_all('#https?://|www\.#', $text);

        // A pitch names what it sells and shows the sender is the one selling.
        if ($topics && $selling && count($selling) + count($nudges) >= 2) {
            return self::result(self::SPAM, 'Sales pitch for '.$topics[0].' ("'.$selling[0].'")');
        }

        if ($topics && ($selling || count($nudges) >= 2)) {
            return self::result(self::REVIEW, 'Possible sales pitch: mentions '.$topics[0]);
        }

        if ($links >= 2) {
            return self::result(self::REVIEW, 'Contains several links');
        }

        if ($age > self::MAX_SECONDS) {
            return self::result(self::REVIEW, 'Form page was open for over a week');
        }

        return self::result(self::OK, null);
    }

    /** @return array{verdict: string, reason: ?string} */
    private static function result(string $verdict, ?string $reason): array
    {
        return ['verdict' => $verdict, 'reason' => $reason];
    }

    /**
     * The phrases from the list that appear as whole words in the text.
     *
     * @param  array<int, string>  $phrases
     * @return array<int, string>
     */
    private static function found(array $phrases, string $text): array
    {
        return array_values(array_filter(
            $phrases,
            fn (string $phrase) => preg_match('/(?<![\p{L}\p{N}])'.preg_quote($phrase, '/').'(?![\p{L}\p{N}])/u', $text) === 1
        ));
    }
}
