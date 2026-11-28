<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans the HTML written in the dashboard's post editor before it is stored.
 *
 * A post body is printed straight into a public page, so only the formatting
 * the editor offers survives: headings, paragraphs, lists, quotes, code,
 * links, images and inline emphasis. Scripts, styles, event handlers, inline
 * CSS and any link or image address that is not a normal web address are
 * removed. The work is done by Symfony's HTML sanitizer; this class only says
 * what the editor is allowed to produce.
 */
class RichText
{
    /** Tag => attributes it may keep. */
    private const ALLOWED = [
        'p' => [], 'br' => [], 'hr' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'code' => [], 'pre' => [],
        'ul' => [], 'ol' => ['start'], 'li' => [],
        'blockquote' => [],
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'figure' => [], 'figcaption' => [],
    ];

    /** Wrappers that are removed while the words inside them are kept. */
    private const UNWRAPPED = [
        'div', 'span', 'section', 'article', 'header', 'footer', 'main', 'aside',
        'h1', 'h5', 'h6', 'font', 'center', 'small', 'big', 'mark', 'sub', 'sup',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption',
        'dl', 'dt', 'dd', 'label',
    ];

    private ?HtmlSanitizer $sanitizer = null;

    public function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $out = trim($this->sanitizer()->sanitize($html));

        // A link to another site opens safely in a new tab. The sanitizer has
        // already quoted and escaped every attribute, so the tag can be read
        // reliably here.
        $out = preg_replace_callback('/<a\s([^>]*)>/i', function (array $match): string {
            if (! preg_match('/\bhref="(?:https?:)?\/\//i', $match[1])) {
                return $match[0];
            }

            return '<a '.$match[1].' rel="noopener noreferrer" target="_blank">';
        }, $out);

        // An image whose address was not safe loses its src; drop what is left.
        $out = preg_replace('/<img(?![^>]*\ssrc=")[^>]*>/i', '', $out);

        // An editor left empty still sends "<p></p>".
        return trim(strip_tags($out, '<img><hr>')) === '' ? null : $out;
    }

    private function sanitizer(): HtmlSanitizer
    {
        if ($this->sanitizer) {
            return $this->sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            // A whole post is far longer than the default limit of 20,000 characters.
            ->withMaxInputLength(-1);

        foreach (self::ALLOWED as $tag => $attributes) {
            $config = $config->allowElement($tag, $attributes);
        }

        foreach (self::UNWRAPPED as $tag) {
            $config = $config->blockElement($tag);
        }

        return $this->sanitizer = new HtmlSanitizer($config);
    }
}
