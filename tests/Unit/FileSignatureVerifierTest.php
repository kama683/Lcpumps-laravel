<?php

namespace Tests\Unit;

use App\Services\FileSignatureVerifier;
use Tests\TestCase;

class FileSignatureVerifierTest extends TestCase
{
    public function test_matches_correct_pdf_signature(): void
    {
        $verifier = new FileSignatureVerifier;

        $this->assertTrue($verifier->matches("%PDF-1.4\n...", '.pdf'));
    }

    public function test_rejects_mismatched_signature(): void
    {
        $verifier = new FileSignatureVerifier;

        $this->assertFalse($verifier->matches('not a real pdf', '.pdf'));
    }

    public function test_rejects_truncated_content(): void
    {
        $verifier = new FileSignatureVerifier;

        $this->assertFalse($verifier->matches('%P', '.pdf'));
    }

    public function test_unregistered_extension_passes_through(): void
    {
        $verifier = new FileSignatureVerifier;

        $this->assertTrue($verifier->matches('anything at all', '.dwg'));
    }

    public function test_matches_png_signature(): void
    {
        $verifier = new FileSignatureVerifier;
        $png = "\x89PNG\x0D\x0A\x1A\x0A".'rest of file';

        $this->assertTrue($verifier->matches($png, '.png'));
        $this->assertFalse($verifier->matches($png, '.pdf'));
    }
}
