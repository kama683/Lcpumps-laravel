<?php

namespace App\Support;

/**
 * The source's cn() = twMerge(clsx(...)) resolves same-property Tailwind
 * class conflicts (e.g. a variant's base "inline-flex" vs a call site's
 * override "hidden tablet:inline-flex") by keeping only the last one.
 * Laravel's $attributes->class() merge is plain string concatenation and
 * does not do this — silently breaking any component whose base classes
 * include a "display" utility a caller means to override (found via the
 * header CTA button rendering visible instead of hidden below the tablet
 * breakpoint: both "inline-flex" and "hidden" matched, and source order in
 * the compiled CSS — not HTML class order — decided the winner).
 *
 * Deliberately narrow: only resolves "display" utility conflicts (the one
 * category actually hit so far), not a full twMerge port.
 */
class Tailwind
{
    private const array DISPLAY_UTILITIES = [
        'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'table',
        'inline-table', 'table-caption', 'flow-root', 'grid', 'inline-grid',
        'contents', 'list-item', 'hidden',
    ];

    public static function merge(string ...$groups): string
    {
        $tokens = [];

        foreach ($groups as $group) {
            foreach (preg_split('/\s+/', trim($group)) as $token) {
                if ($token === '') {
                    continue;
                }

                $bare = preg_replace('/^.*:/', '', $token);

                if (in_array($bare, self::DISPLAY_UTILITIES, true)) {
                    $variant = substr($token, 0, strlen($token) - strlen($bare));
                    $tokens = array_filter($tokens, function (string $existing) use ($variant): bool {
                        if (! str_starts_with($existing, $variant)) {
                            return true;
                        }
                        $existingBare = substr($existing, strlen($variant));

                        return ! in_array($existingBare, self::DISPLAY_UTILITIES, true);
                    });
                }

                $tokens[] = $token;
            }
        }

        return implode(' ', $tokens);
    }
}
