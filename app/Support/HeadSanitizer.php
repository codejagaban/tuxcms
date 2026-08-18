<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Strips anything dangerous from the free-form `custom_head` field before it is
 * rendered into <head>.
 *
 * The field exists so an editor can paste verification meta tags — not so they
 * can inject scripts. Only a narrow allow-list survives.
 */
class HeadSanitizer
{
    private const ALLOWED_TAGS = ['meta', 'link', 'title'];

    private const ALLOWED_ATTRS = [
        'name', 'content', 'property', 'charset', 'http-equiv',
        'rel', 'href', 'hreflang', 'type', 'sizes', 'media', 'as', 'crossorigin',
    ];

    public function sanitize(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><head>' . $html . '</head></html>',
            LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $head = $doc->getElementsByTagName('head')->item(0);

        if (!$head) {
            return '';
        }

        $output = [];

        foreach (iterator_to_array($head->childNodes) as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if (!in_array(strtolower($node->tagName), self::ALLOWED_TAGS, true)) {
                continue;
            }

            if ($clean = $this->cleanElement($doc, $node)) {
                $output[] = $clean;
            }
        }

        return implode("\n", $output);
    }

    private function cleanElement(DOMDocument $doc, DOMElement $node): ?string
    {
        $tag = strtolower($node->tagName);
        $attrs = [];

        foreach (iterator_to_array($node->attributes ?? []) as $attr) {
            $name = strtolower($attr->name);
            $value = $attr->value;

            // No event handlers, ever.
            if (str_starts_with($name, 'on')) {
                continue;
            }

            if (!in_array($name, self::ALLOWED_ATTRS, true)) {
                continue;
            }

            // Block script-bearing URL schemes.
            if (in_array($name, ['href'], true) && $this->isDangerousUrl($value)) {
                continue;
            }

            $attrs[] = sprintf('%s="%s"', $name, e($value));
        }

        if ($attrs === []) {
            return null;
        }

        return sprintf('<%s %s>', $tag, implode(' ', $attrs));
    }

    private function isDangerousUrl(string $url): bool
    {
        $normalized = strtolower(preg_replace('/\s+/', '', $url));

        return str_starts_with($normalized, 'javascript:')
            || str_starts_with($normalized, 'data:text/html')
            || str_starts_with($normalized, 'vbscript:');
    }
}
