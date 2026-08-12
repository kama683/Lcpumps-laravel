<?php

namespace App\Support;

/**
 * Ported from data/pump-dimensions.ts + components/contact/PumpDimensionsTable.tsx's
 * computeSpans(). resources/data/pump-dimensions.json is a one-time export of the
 * source's static array (same throwaway-script approach as projects.json/company.json).
 */
class PumpDimensions
{
    /** Columns whose repeated values within a series group are merged via rowspan. */
    public const array MERGED_KEYS = [
        'B1', 'B2', 'B', 'H', 'h0', 'h', 'h1', 'h2', 'H1', 'L3', 'L4', 'P', 't', 'nd', 'baseWeight',
    ];

    /** Header sub-columns under "Установочные размеры", in display order (includes non-merged L/L0/L5). */
    public const array DIMENSION_COLUMNS = [
        'B1', 'B2', 'B', 'H', 'h0', 'h', 'h1', 'h2', 'H1', 'L', 'L0', 'L3', 'L4', 'P', 'L5', 't',
    ];

    private static ?array $groups = null;

    /** @return array<int, array{series: string, rows: array<int, array<string, mixed>>}> */
    public static function groups(): array
    {
        return self::$groups ??= json_decode(
            file_get_contents(resource_path('data/pump-dimensions.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, array<int, int>> spans[key][rowIndex] — 0 means "covered by an earlier row's rowspan"
     */
    public static function computeSpans(array $rows): array
    {
        $n = count($rows);
        $spans = [];

        foreach (self::MERGED_KEYS as $key) {
            $col = array_fill(0, $n, 0);
            $i = 0;
            while ($i < $n) {
                $j = $i + 1;
                while ($j < $n && $rows[$j][$key] === $rows[$i][$key]) {
                    $j++;
                }
                $col[$i] = $j - $i;
                $i = $j;
            }
            $spans[$key] = $col;
        }

        return $spans;
    }
}
