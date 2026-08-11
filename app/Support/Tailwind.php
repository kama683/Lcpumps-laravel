<?php

namespace App\Support;

/**
 * The source's cn() = twMerge(clsx(...)) resolves same-property Tailwind
 * class conflicts (e.g. a variant's base "inline-flex" vs a call site's
 * override "hidden tablet:inline-flex") by keeping only the last one.
 * Laravel's $attributes->class() merge is plain string concatenation and
 * does not do this — silently breaking any component whose base classes
 * include a utility from one of the groups below that a caller means to
 * override. Found via the header CTA button rendering visible instead of
 * hidden below the tablet breakpoint: both "inline-flex" and "hidden"
 * matched, and source order in the compiled CSS — not HTML class order —
 * decided the winner.
 *
 * Applied to every component below that accepts a passthrough class,
 * regardless of whether a conflict has been proven at a current call site
 * — new call sites in later Stage 5 batches can introduce one silently
 * (see the header CTA case), so this isn't reactive-patch-only.
 *
 * Deliberately narrow: only resolves conflicts within these two utility
 * groups (the ones actually found in this codebase's variant classes —
 * display on Button/Card, position on AboutVideo/HeroSlider/ProductImage/
 * ProjectImage), not a full twMerge port.
 */
class Tailwind
{
    private const array CONFLICT_GROUPS = [
        [
            'block', 'inline-block', 'inline', 'flex', 'inline-flex', 'table',
            'inline-table', 'table-caption', 'flow-root', 'grid', 'inline-grid',
            'contents', 'list-item', 'hidden',
        ],
        ['static', 'fixed', 'absolute', 'relative', 'sticky'],
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
                $conflictGroup = self::findConflictGroup($bare);

                if ($conflictGroup !== null) {
                    $variant = substr($token, 0, strlen($token) - strlen($bare));
                    $tokens = array_filter($tokens, function (string $existing) use ($variant, $conflictGroup): bool {
                        if (! str_starts_with($existing, $variant)) {
                            return true;
                        }
                        $existingBare = substr($existing, strlen($variant));

                        return ! in_array($existingBare, $conflictGroup, true);
                    });
                }

                $tokens[] = $token;
            }
        }

        return implode(' ', $tokens);
    }

    private static function findConflictGroup(string $bareToken): ?array
    {
        foreach (self::CONFLICT_GROUPS as $group) {
            if (in_array($bareToken, $group, true)) {
                return $group;
            }
        }

        return null;
    }
}
