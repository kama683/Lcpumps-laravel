<?php

namespace App\Services;

use App\Mail\NewContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Ported from components/contact/actions.ts's submitContactAction. Checks
 * run in the same order as the source: honeypot, rate limit, required
 * fields, email format, then per-file (extension, size, signature), then
 * total attachment size, then persistence. Error codes are returned as-is
 * (see lang/*\/Contact.php, key form.errors.*) rather than translated here,
 * so the caller decides how to surface them.
 */
class ContactSubmissionHandler
{
    /** @var string[] */
    private const array ALLOWED_EXTENSIONS = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.dwg', '.png', '.jpg', '.jpeg'];

    private const int MAX_FILE_SIZE = 15 * 1024 * 1024;

    private const int MAX_TOTAL_SIZE = 40 * 1024 * 1024;

    // Longest signature check (webp) reads up to offset 8 + 4 bytes; a
    // 16-byte header covers every registered signature with margin, so the
    // rest of a 15 MB file never has to be read into memory.
    private const int SIGNATURE_HEADER_BYTES = 16;

    private const string EMAIL_PATTERN = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    // Real customers submit this form once in a while, not repeatedly — this
    // only needs to stop scripted flooding.
    private const int RATE_LIMIT_MAX_SUBMISSIONS = 5;

    private const int RATE_LIMIT_WINDOW_SECONDS = 10 * 60;

    /**
     * Canonical field order — public because NewContactSubmissionMail reuses
     * it too: tech_primary/tech_extra are stored as JSON, and MySQL's JSON
     * type does not preserve object key insertion order the way sqlite's
     * does (see tests/Feature/ContactFormTest.php), so anything that
     * displays these fields must order them from this list, never from the
     * decoded array's own key order.
     *
     * @var string[]
     */
    public const array PRIMARY_TECH_KEYS = [
        'model', 'flow', 'head', 'power', 'speed', 'efficiency',
        'temperature', 'inlet_diameter', 'outlet_diameter', 'pump_material',
    ];

    /** @var string[] */
    public const array EXTRA_TECH_KEYS = [
        'motor', 'voltage', 'protection_level', 'cooling_method', 'motor_efficiency', 'weight',
    ];

    public function __construct(
        private readonly RateLimiterService $rateLimiter,
        private readonly FileSignatureVerifier $signatures,
        private readonly PrivateAttachmentStorage $storage,
    ) {}

    /** @return array{status: 'success'|'error', errorCode?: string, errorDetail?: string} */
    public function handle(Request $request): array
    {
        // Honeypot: a real visitor never sees or fills this field (hidden via
        // CSS in the contact form). Bots that fill every input trip it —
        // pretend success without persisting anything so they don't retry
        // with a fix.
        if (trim((string) $request->input('website', '')) !== '') {
            return ['status' => 'success'];
        }

        $ip = $request->ip() ?? 'unknown';
        if (! $this->rateLimiter->attempt("contact:{$ip}", self::RATE_LIMIT_MAX_SUBMISSIONS, self::RATE_LIMIT_WINDOW_SECONDS)) {
            return ['status' => 'error', 'errorCode' => 'rateLimited'];
        }

        $name = trim((string) $request->input('name', ''));
        $company = trim((string) $request->input('company', ''));
        $email = trim((string) $request->input('email', ''));
        $phone = trim((string) $request->input('phone', ''));
        $message = trim((string) $request->input('message', ''));
        $mode = $request->input('mode') === 'technical' ? 'technical' : 'basic';

        if ($name === '' || $email === '') {
            return ['status' => 'error', 'errorCode' => 'missingFields'];
        }
        if (! preg_match(self::EMAIL_PATTERN, $email)) {
            return ['status' => 'error', 'errorCode' => 'invalidEmail'];
        }

        $techPrimary = $mode === 'technical' ? $this->collectTechFields($request, self::PRIMARY_TECH_KEYS) : null;
        $techExtra = $mode === 'technical' ? $this->collectTechFields($request, self::EXTRA_TECH_KEYS) : null;

        /** @var UploadedFile[] $files */
        $files = array_values(array_filter(
            (array) $request->file('attachments', []),
            fn ($file) => $file instanceof UploadedFile && $file->getSize() > 0
        ));

        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += $file->getSize();
            $extension = $this->extension($file);

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                return ['status' => 'error', 'errorCode' => 'invalidFileType', 'errorDetail' => $file->getClientOriginalName()];
            }
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                return ['status' => 'error', 'errorCode' => 'fileTooLarge', 'errorDetail' => $file->getClientOriginalName()];
            }
            if (! $this->signatures->matches($this->readHeader($file), $extension)) {
                return ['status' => 'error', 'errorCode' => 'invalidFileType', 'errorDetail' => $file->getClientOriginalName()];
            }
        }
        if ($totalSize > self::MAX_TOTAL_SIZE) {
            return ['status' => 'error', 'errorCode' => 'totalSizeExceeded'];
        }

        // Attachments are written to disk *before* opening the DB
        // transaction, and cleaned up again on any failure below. Disk
        // writes don't participate in the DB transaction's rollback, so
        // doing it the other way around (inside the transaction) risks
        // leaving orphaned files on disk with no matching DB row if the
        // attachment insert fails partway through a multi-file submission.
        $attachmentInputs = [];
        try {
            foreach ($files as $file) {
                $saved = $this->storage->save($file, $this->extension($file));
                $attachmentInputs[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'storage_path' => $saved['path'],
                ];
            }
        } catch (Throwable $e) {
            Log::error('Contact submission failed to store attachments', ['exception' => $e]);
            $this->cleanupStoredAttachments($attachmentInputs);

            return ['status' => 'error', 'errorCode' => 'unknown'];
        }

        try {
            $submission = DB::transaction(function () use ($mode, $name, $company, $email, $phone, $message, $techPrimary, $techExtra, $attachmentInputs) {
                $submission = ContactSubmission::create([
                    'mode' => $mode,
                    'name' => $name,
                    'company' => $company,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $message,
                    'tech_primary' => $techPrimary,
                    'tech_extra' => $techExtra,
                ]);

                foreach ($attachmentInputs as $input) {
                    $submission->attachments()->create($input);
                }

                return $submission;
            });
        } catch (Throwable $e) {
            Log::error('Contact submission failed to save', ['exception' => $e]);
            $this->cleanupStoredAttachments($attachmentInputs);

            return ['status' => 'error', 'errorCode' => 'unknown'];
        }

        // Notification email is a Stage 6 addition, not present in the
        // source. It must never take down a submission that already made it
        // to the database — log and move on if sending fails.
        try {
            Mail::to((string) config('mail.contact_notify_to'))->send(new NewContactSubmissionMail($submission));
        } catch (Throwable $e) {
            Log::error('Failed to send contact notification email', ['submission_id' => $submission->id, 'exception' => $e]);
        }

        return ['status' => 'success'];
    }

    /** @param  string[]  $keys
     * @return array<string, string>|null */
    private function collectTechFields(Request $request, array $keys): ?array
    {
        $values = [];
        foreach ($keys as $key) {
            $value = trim((string) $request->input($key, ''));
            if ($value !== '') {
                $values[$key] = $value;
            }
        }

        return $values === [] ? null : $values;
    }

    private function extension(UploadedFile $file): string
    {
        $name = $file->getClientOriginalName();
        $idx = strrpos($name, '.');

        return $idx === false ? '' : strtolower(substr($name, $idx));
    }

    /** Reads only the first bytes of the upload for the magic-byte check, instead of the whole file. */
    private function readHeader(UploadedFile $file): string
    {
        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return '';
        }

        try {
            return (string) fread($handle, self::SIGNATURE_HEADER_BYTES);
        } finally {
            fclose($handle);
        }
    }

    /** @param  array<int, array{storage_path: string}>  $attachmentInputs */
    private function cleanupStoredAttachments(array $attachmentInputs): void
    {
        foreach ($attachmentInputs as $input) {
            $this->storage->delete($input['storage_path']);
        }
    }
}
