<?php

namespace App\Support;

/**
 * Ports the locale-aware parts of data/content.ts + lib/catalog-helpers.ts's
 * getCompanyParagraphs. resources/data/company.json is a one-time export of
 * catalog.json's `company` key (12 paragraphs per locale, ru/kk/en/zh — same
 * throwaway-script export approach as ProjectsRepository's projects.json).
 *
 * Per KNOWN_DEFECTS.md #2: several source call sites invoke
 * getCompanyParagraphs()/getApplicationsCards() with NO locale argument,
 * which silently falls back to the "ru" default — a real bug on the live
 * site that this port preserves byte-for-byte. companyParagraphs() below
 * mirrors that default (locale = 'ru') so call sites can reproduce the bug
 * simply by omitting the argument, same as the source.
 */
class Content
{
    private static ?array $company = null;

    private static function company(): array
    {
        return self::$company ??= json_decode(
            file_get_contents(resource_path('data/company.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    /** @return array<int, string> */
    public static function companyParagraphs(string $locale = 'ru'): array
    {
        return self::company()[$locale] ?? self::company()['ru'];
    }

    /** Finds a paragraph index in the canonical Russian source, so keyword
     * matching stays correct regardless of which locale's text is displayed. */
    private static function findCompanyIndex(string $pattern): int
    {
        foreach (self::companyParagraphs('ru') as $i => $paragraph) {
            if (preg_match($pattern, $paragraph) === 1) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * Ports data/content.ts's getApplicationsCards(). Card titles are
     * literal Russian strings in the source (not translated anywhere, see
     * KNOWN_DEFECTS.md #2) except the first, which happens to duplicate the
     * already-translated Applications.title lang key.
     *
     * @return array<int, array{title: string, text: string}>
     */
    public static function applicationsCards(string $locale = 'ru'): array
    {
        $company = self::companyParagraphs($locale);
        $buildingIdx = self::findCompanyIndex('/строительств/iu');
        $industrialIdx = self::findCompanyIndex('/промышленн/iu');

        return [
            ['title' => 'Отрасли применения', 'text' => $company[2] ?? ''],
            ['title' => 'Строительство зданий', 'text' => ($buildingIdx >= 0 ? $company[$buildingIdx] : $company[2]) ?? ''],
            ['title' => 'Промышленные и горнодобывающие предприятия', 'text' => ($industrialIdx >= 0 ? $company[$industrialIdx] : $company[2]) ?? ''],
            ['title' => 'Проекты', 'text' => $company[7] ?? ''],
        ];
    }
}
