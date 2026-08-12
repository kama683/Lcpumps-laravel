<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ported from lib/storage/local.ts's publicStorage instance, used only by
 * the product-image upload endpoint. Deliberately a separate service from
 * PrivateAttachmentStorage (Stage 6) rather than one storage service
 * reused for both: different disk (public vs. private/local), different
 * size cap, different allowed types — collapsing them "just in case" would
 * make it easy to accidentally serve a contact attachment publicly or vice
 * versa.
 *
 * Files land on the "public" disk (storage/app/public/..., symlinked to
 * public/storage via `php artisan storage:link` — see DEPLOY.md), which
 * Plesk does serve directly.
 */
class PublicProductImageStorage
{
    /** @return array{path: string, url: string} */
    public function save(string $contents, string $slug, string $extension): array
    {
        $filename = Str::uuid()->toString().$extension;
        $path = "products/{$slug}/{$filename}";

        Storage::disk('public')->put($path, $contents);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ];
    }
}
