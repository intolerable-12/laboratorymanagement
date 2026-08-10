<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichTextSanitizer
{
    /**
     * Allowed HTML tags from the editor.
     *
     * @var array<int, string>
     */
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        's',
        'strike',
        'blockquote',
        'ul',
        'ol',
        'li',
        'a',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'pre',
        'code',
        'span',
    ];

    public static function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        if (trim(strip_tags($html)) === '') {
            return null;
        }

        $previousState = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="wrapper">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $wrapper = $dom->getElementById('wrapper');

        if (! $wrapper) {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);

            return strip_tags($html, self::allowedTagsString()) ?: null;
        }

        self::sanitizeNode($wrapper);

        $sanitized = '';

        foreach (iterator_to_array($wrapper->childNodes) as $childNode) {
            $sanitized .= $dom->saveHTML($childNode);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $sanitized = trim($sanitized);

        return $sanitized !== '' ? $sanitized : null;
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        for ($index = $node->childNodes->length - 1; $index >= 0; $index--) {
            $childNode = $node->childNodes->item($index);

            if (! $childNode) {
                continue;
            }

            if ($childNode->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tagName = strtolower($childNode->nodeName);

            if (! in_array($tagName, self::ALLOWED_TAGS, true)) {
                self::unwrapNode($node, $childNode);
                continue;
            }

            if ($childNode instanceof DOMElement) {
                self::sanitizeElement($childNode);
            }

            self::sanitizeNode($childNode);
        }
    }

    private static function sanitizeElement(DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $allowedHref = $tagName === 'a' ? self::filterHref($element->getAttribute('href')) : null;

        while ($element->attributes->length > 0) {
            $element->removeAttributeNode($element->attributes->item(0));
        }

        if ($tagName === 'a' && $allowedHref !== null) {
            $element->setAttribute('href', $allowedHref);
            $element->setAttribute('rel', 'noreferrer noopener nofollow');

            if (preg_match('/^https?:\/\//i', $allowedHref) === 1) {
                $element->setAttribute('target', '_blank');
            }
        }
    }

    private static function filterHref(?string $href): ?string
    {
        $href = trim((string) $href);

        if ($href === '') {
            return null;
        }

        if (preg_match('/^(https?:|mailto:|tel:|\/|#)/i', $href) !== 1) {
            return null;
        }

        return $href;
    }

    private static function unwrapNode(DOMNode $parentNode, DOMNode $node): void
    {
        while ($node->firstChild) {
            $parentNode->insertBefore($node->firstChild, $node);
        }

        $parentNode->removeChild($node);
    }

    private static function allowedTagsString(): string
    {
        return '<' . implode('><', self::ALLOWED_TAGS) . '>';
    }
}