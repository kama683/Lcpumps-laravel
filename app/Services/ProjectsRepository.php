<?php

namespace App\Services;

/**
 * Reads resources/data/projects.json — a static (non-DB-backed) export of
 * data/projects.ts's getProjectsData(), produced by a one-time tsx script
 * run against the Next.js source (see Stage 5 commit message). Projects
 * aren't admin-editable in the source project either, so a static JSON
 * file is a faithful port, not a simplification.
 */
class ProjectsRepository
{
    private static ?array $data = null;

    private static function data(): array
    {
        return self::$data ??= json_decode(
            file_get_contents(resource_path('data/projects.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        )['byLocale'];
    }

    /** @return array{heroIntro:string,compact:array,featured:array,industries:array} */
    public static function forLocale(string $locale): array
    {
        return self::data()[$locale] ?? self::data()['ru'];
    }

    public static function bySlug(string $slug, string $locale): ?array
    {
        foreach (self::forLocale($locale)['featured'] as $project) {
            if ($project['slug'] === $slug) {
                return $project;
            }
        }

        return null;
    }
}
