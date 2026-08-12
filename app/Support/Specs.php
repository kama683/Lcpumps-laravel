<?php

namespace App\Support;

/** Ported from lib/catalog-helpers.ts's parseSpecRow. */
class Specs
{
    /** @return array{0: string, 1: string} [key, value] */
    public static function parseRow(string $spec): array
    {
        if (str_contains($spec, ':')) {
            [$key, $value] = explode(':', $spec, 2);

            return [trim($key), trim($value)];
        }

        return [$spec, ''];
    }
}
