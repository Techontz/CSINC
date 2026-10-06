<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes editor-authored HTML before it is exposed through the public API.
 */
class RichText
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        return self::sanitizer()->sanitize($html);
    }

    public static function plain(?string $html, int $limit = 160): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))));

        return $text === '' ? null : str($text)->limit($limit, '…')->toString();
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->blockElement('img')
                ->allowRelativeLinks()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(200_000)
        );
    }
}
