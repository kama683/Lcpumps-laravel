<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ported from lib/storage/local.ts's privateStorage instance. Files land on
 * the "local" disk (storage/app/private/..., see config/filesystems.php),
 * which Plesk never serves directly — attachments are only reachable through
 * an authenticated admin download route (Stage 7).
 */
class PrivateAttachmentStorage
{
    /**
     * @return array{path: string} Relative path on the "local" disk, for persisting to submission_attachments.storage_path.
     *
     * Uses UploadedFile::storeAs(), which streams/moves the upload rather
     * than reading it into memory — matters on shared hosting where
     * memory_limit is typically well under the 40 MB total attachment cap.
     */
    public function save(UploadedFile $file, string $extension): array
    {
        $subdir = trim((string) config('filesystems.uploads_private_dir'), '/');
        $filename = Str::uuid()->toString().$extension;

        $path = $file->storeAs($subdir, $filename, 'local');

        return ['path' => $path];
    }

    /** Best-effort cleanup for files that were saved to disk but whose DB write then failed. */
    public function delete(string $path): void
    {
        Storage::disk('local')->delete($path);
    }
}
