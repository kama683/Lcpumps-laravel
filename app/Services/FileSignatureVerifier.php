<?php

namespace App\Services;

/**
 * Ported from lib/file-signature.ts. Best-effort magic-byte check layered on
 * top of the extension/MIME allowlist already enforced at each call site.
 * Extensions with no entry here (e.g. .dwg — AutoCAD's version-tagged header
 * varies too much across releases to pin down safely) are treated as
 * unverifiable and pass through rather than risk rejecting a legitimate
 * file.
 */
class FileSignatureVerifier
{
    /** @var array<string, callable(string):bool> */
    private readonly array $signatures;

    public function __construct()
    {
        $this->signatures = [
            '.jpg' => fn (string $contents) => $this->bytesMatch($contents, 0, [0xFF, 0xD8, 0xFF]),
            '.jpeg' => fn (string $contents) => $this->bytesMatch($contents, 0, [0xFF, 0xD8, 0xFF]),
            '.png' => fn (string $contents) => $this->bytesMatch($contents, 0, [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A]),
            '.webp' => fn (string $contents) => $this->bytesMatch($contents, 0, [0x52, 0x49, 0x46, 0x46])
                && $this->bytesMatch($contents, 8, [0x57, 0x45, 0x42, 0x50]),
            '.pdf' => fn (string $contents) => $this->bytesMatch($contents, 0, [0x25, 0x50, 0x44, 0x46]),
            // OLE2 compound file — legacy .doc/.xls share this container format.
            '.doc' => fn (string $contents) => $this->bytesMatch($contents, 0, [0xD0, 0xCF, 0x11, 0xE0, 0xA1, 0xB1, 0x1A, 0xE1]),
            '.xls' => fn (string $contents) => $this->bytesMatch($contents, 0, [0xD0, 0xCF, 0x11, 0xE0, 0xA1, 0xB1, 0x1A, 0xE1]),
            // OOXML — .docx/.xlsx are ZIP archives.
            '.docx' => fn (string $contents) => $this->bytesMatch($contents, 0, [0x50, 0x4B, 0x03, 0x04]),
            '.xlsx' => fn (string $contents) => $this->bytesMatch($contents, 0, [0x50, 0x4B, 0x03, 0x04]),
        ];
    }

    public function matches(string $contents, string $extension): bool
    {
        $check = $this->signatures[strtolower($extension)] ?? null;

        return $check === null ? true : $check($contents);
    }

    /** @param  int[]  $bytes */
    private function bytesMatch(string $contents, int $offset, array $bytes): bool
    {
        if (strlen($contents) < $offset + count($bytes)) {
            return false;
        }

        foreach ($bytes as $i => $byte) {
            if (ord($contents[$offset + $i]) !== $byte) {
                return false;
            }
        }

        return true;
    }
}
