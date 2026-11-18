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

    /**
     * @param  array<int, string|null>  $texts  the free-text parts of the submission
     * @param  mixed  $elapsed  seconds the visitor had the form open, sent by the site script
     */
    public static function isSpam(array $texts, mixed $elapsed): bool
    {
        // The site script always reports how long the form was open. A missing
        // or tiny value means something posted straight to the address.
        if (! is_numeric($elapsed) || (float) $elapsed < self::MIN_SECONDS) {
            return true;
        }

        $text = ' '.mb_strtolower(implode(' ', array_filter($texts))).' ';

        if ($text === '  ') {
            return false;
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
