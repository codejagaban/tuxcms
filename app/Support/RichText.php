<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Cleans the HTML written in the dashboard's post editor before it is stored.
 *
 * A post body is printed straight into a public page, so only the formatting
 * the editor offers survives: headings, paragraphs, lists, quotes, links,
 * images and inline emphasis. Scripts, styles, event handlers, inline CSS and
 * any link or image address that is not a normal web address are removed.
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

    /** Removed together with everything inside them. */
    private const DROPPED = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'noscript', 'template', 'svg', 'math', 'link', 'meta', 'base'];

    public function clean(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="rich-text-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('rich-text-root');
        if (! $root) {
            return null;
        }

        $this->cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        $out = trim($out);

        // An editor left empty still sends "<p></p>".
        return trim(strip_tags($out, '<img><hr>')) === '' ? null : $out;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);

                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->nodeName);

            if (in_array($tag, self::DROPPED, true)) {
                $parent->removeChild($node);

                continue;
            }

            $this->cleanChildren($node);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Keep the words, lose the wrapper.
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            foreach (iterator_to_array($node->attributes) as $attribute) {
                if (! in_array(strtolower($attribute->nodeName), self::ALLOWED[$tag], true)) {
                    $node->removeAttribute($attribute->nodeName);
                }
            }

            if ($tag === 'a') {
                $this->cleanLink($node);
            }

            if ($tag === 'img' && ! $this->safeAddress($node->getAttribute('src'), image: true)) {
                $parent->removeChild($node);
            }
        }
    }

    private function cleanLink(DOMElement $link): void
    {
        $href = trim($link->getAttribute('href'));

        if (! $this->safeAddress($href)) {
            $link->removeAttribute('href');

            return;
        }

        // A link to another site opens safely in a new tab.
        if (preg_match('#^https?://#i', $href)) {
            $link->setAttribute('rel', 'noopener noreferrer');
            $link->setAttribute('target', '_blank');
        }
    }

    /** Web addresses, paths on this site, and (for links) email, phone and anchors. */
    private function safeAddress(string $address, bool $image = false): bool
    {
        // Control characters and spaces can hide a scheme from the check below.
        $address = preg_replace('/[\x00-\x20]+/', '', $address);

        if ($address === '') {
            return false;
        }

        if (preg_match('#^(https?:)?//#i', $address) || (str_starts_with($address, '/') )) {
            return true;
        }

        if ($image) {
            return false;
        }

        return (bool) preg_match('/^(mailto:|tel:|#)/i', $address);
    }
}
