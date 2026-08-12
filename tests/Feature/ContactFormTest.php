<?php

namespace Tests\Feature;

use App\Mail\NewContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['mail.contact_notify_to' => 'sales@lcpumps.kz']);
    }

    private function validPdf(string $name = 'spec.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n%mock pdf content for tests");
    }

    public function test_basic_submission_succeeds_and_notifies(): void
    {
        Mail::fake();

        $response = $this->post(route('contact.submit'), [
            'mode' => 'basic',
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'company' => 'ТОО Ромашка',
            'phone' => '+7 700 000 00 00',
            'message' => 'Нужен насос',
        ]);

        $response->assertRedirect(route('contact.index'));
        $response->assertSessionHas('contact_success', true);
        $response->assertSessionHasNoErrors();

        $this->assertSame(1, ContactSubmission::query()->count());
        $submission = ContactSubmission::query()->first();
        $this->assertSame('basic', $submission->mode);
        $this->assertSame('Иван Петров', $submission->name);
        $this->assertNull($submission->tech_primary);
        $this->assertNull($submission->tech_extra);

        Mail::assertSent(NewContactSubmissionMail::class, fn ($mail) => $mail->submission->is($submission));
    }

    public function test_technical_submission_stores_non_empty_tech_fields_and_attachment(): void
    {
        Mail::fake();

        $response = $this->post(route('contact.submit'), [
            'mode' => 'technical',
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'model' => 'LC-100',
            'flow' => '50',
            'head' => '',
            'motor' => '',
            'voltage' => '380',
            'attachments' => [$this->validPdf()],
        ]);

        $response->assertRedirect(route('contact.index'));
        $response->assertSessionHas('contact_success', true);

        $submission = ContactSubmission::query()->firstOrFail();
        $this->assertSame('technical', $submission->mode);
        // assertEqualsCanonicalizing, not assertSame: MySQL's native JSON
        // type does not preserve object key insertion order the way
        // sqlite's does, and nothing in the app depends on that order
        // (lookups are always by key).
        $this->assertEqualsCanonicalizing(['model' => 'LC-100', 'flow' => '50'], $submission->tech_primary);
        $this->assertEqualsCanonicalizing(['voltage' => '380'], $submission->tech_extra);
        $this->assertSame(1, $submission->attachments()->count());

        $attachment = $submission->attachments()->first();
        $this->assertSame('spec.pdf', $attachment->file_name);
        Storage::disk('local')->assertExists($attachment->storage_path);
    }

    public function test_notification_email_lists_technical_fields_in_canonical_order_not_storage_order(): void
    {
        // Deliberately reversed relative to
        // ContactSubmissionHandler::PRIMARY_TECH_KEYS (model before
        // pump_material there) — proves the view orders by the canonical
        // list, not by however the array happens to be keyed (which is all
        // MySQL's JSON type guarantees, see the note on that constant).
        $submission = ContactSubmission::create([
            'mode' => 'technical',
            'name' => 'Иван',
            'company' => '',
            'email' => 'ivan@example.com',
            'phone' => '',
            'message' => '',
            'tech_primary' => ['pump_material' => 'Чугун', 'model' => 'LC-100'],
            'tech_extra' => null,
        ]);

        $html = (new NewContactSubmissionMail($submission))->render();

        $modelPos = mb_strpos($html, 'Модель');
        $materialPos = mb_strpos($html, 'Материал насоса');

        $this->assertNotFalse($modelPos);
        $this->assertNotFalse($materialPos);
        $this->assertLessThan($materialPos, $modelPos);
    }

    public function test_technical_mode_with_no_filled_tech_fields_stores_null(): void
    {
        Mail::fake();

        $this->post(route('contact.submit'), [
            'mode' => 'technical',
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
        ]);

        $submission = ContactSubmission::query()->firstOrFail();
        $this->assertNull($submission->tech_primary);
        $this->assertNull($submission->tech_extra);
    }

    public function test_unrecognized_mode_falls_back_to_basic(): void
    {
        Mail::fake();

        $this->post(route('contact.submit'), [
            'mode' => 'bogus',
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
        ]);

        $this->assertSame('basic', ContactSubmission::query()->firstOrFail()->mode);
    }

    public function test_honeypot_pretends_success_without_saving(): void
    {
        Mail::fake();

        $response = $this->post(route('contact.submit'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect(route('contact.index'));
        $response->assertSessionHas('contact_success', true);

        $this->assertSame(0, ContactSubmission::query()->count());
        Mail::assertNothingSent();
    }

    public function test_missing_required_fields_returns_error_without_saving(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['contact' => __('Contact.form.errors.missingFields')]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_invalid_email_returns_error(): void
    {
        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['contact' => __('Contact.form.errors.invalidEmail')]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_disallowed_extension_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('virus.exe', 'MZ...');

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => [$file],
        ]);

        $response->assertSessionHasErrors([
            'contact' => __('Contact.form.errors.invalidFileType', ['detail' => 'virus.exe']),
        ]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_file_exceeding_signature_check_is_rejected_despite_allowed_extension(): void
    {
        $file = UploadedFile::fake()->createWithContent('fake.pdf', 'this is not really a pdf');

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => [$file],
        ]);

        $response->assertSessionHasErrors([
            'contact' => __('Contact.form.errors.invalidFileType', ['detail' => 'fake.pdf']),
        ]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_unverifiable_extension_dwg_passes_signature_check(): void
    {
        Mail::fake();
        $file = UploadedFile::fake()->createWithContent('drawing.dwg', 'anything goes for dwg');

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => [$file],
        ]);

        $response->assertSessionHas('contact_success', true);
        $this->assertSame(1, ContactSubmission::query()->firstOrFail()->attachments()->count());
    }

    public function test_file_over_15mb_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('big.pdf', 15 * 1024 + 1);

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => [$file],
        ]);

        $response->assertSessionHasErrors([
            'contact' => __('Contact.form.errors.fileTooLarge', ['detail' => 'big.pdf']),
        ]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_total_attachment_size_over_40mb_is_rejected(): void
    {
        $files = [
            UploadedFile::fake()->create('a.dwg', 14000),
            UploadedFile::fake()->create('b.dwg', 14000),
            UploadedFile::fake()->create('c.dwg', 14000),
        ];

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => $files,
        ]);

        $response->assertSessionHasErrors([
            'contact' => __('Contact.form.errors.totalSizeExceeded'),
        ]);
        $this->assertSame(0, ContactSubmission::query()->count());
    }

    public function test_rate_limit_blocks_after_five_submissions_from_same_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.submit'), [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
            ])->assertSessionHas('contact_success', true);
        }

        $response = $this->post(route('contact.submit'), [
            'name' => 'User 6',
            'email' => 'user6@example.com',
        ]);

        $response->assertSessionHasErrors(['contact' => __('Contact.form.errors.rateLimited')]);
        $this->assertSame(5, ContactSubmission::query()->count());
    }

    public function test_disk_files_are_cleaned_up_when_the_database_save_fails(): void
    {
        Mail::fake();
        ContactSubmission::creating(function () {
            throw new \RuntimeException('DB unavailable');
        });

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'attachments' => [$this->validPdf()],
        ]);

        $response->assertSessionHasErrors(['contact' => __('Contact.form.errors.unknown')]);
        $this->assertSame(0, ContactSubmission::query()->count());
        $this->assertCount(0, Storage::disk('local')->allFiles(), 'orphaned attachment left on disk after a failed DB save');
    }

    /**
     * PHP itself drops $_POST/$_FILES when Content-Length exceeds
     * post_max_size, before Laravel's session/CSRF middleware ever runs —
     * Laravel's test helpers build requests in-process and never populate a
     * real Content-Length, so this can't be triggered end-to-end via
     * $this->post(). Testing the registered PostTooLargeException render
     * callback directly (bootstrap/app.php) is the reliable way to cover it.
     */
    public function test_post_too_large_redirects_with_a_whitelisted_error_code(): void
    {
        $request = Request::create('/contact', 'POST');

        $response = app(ExceptionHandler::class)->render($request, new PostTooLargeException);

        $this->assertTrue($response->isRedirect());
        parse_str((string) parse_url((string) $response->headers->get('Location'), PHP_URL_QUERY), $query);
        // A bare code, not translated text — the query string is
        // attacker-writable, so the Blade page must whitelist this value
        // before rendering it, never echo it directly. See
        // ContactController::submit's doc block.
        $this->assertSame('totalSizeExceeded', $query['contactFormError'] ?? null);
    }

    public function test_post_too_large_preserves_locale_prefix(): void
    {
        $request = Request::create('/kk/contact', 'POST');

        $response = app(ExceptionHandler::class)->render($request, new PostTooLargeException);

        $location = (string) $response->headers->get('Location');
        $this->assertStringContainsString('/kk/contact', $location);

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('totalSizeExceeded', $query['contactFormError'] ?? null);
    }

    public function test_post_too_large_on_unrelated_route_falls_through_to_default_handling(): void
    {
        $request = Request::create('/products', 'POST');

        $response = app(ExceptionHandler::class)->render($request, new PostTooLargeException);

        $this->assertSame(413, $response->getStatusCode());
    }

    public function test_submission_is_saved_even_when_notification_email_fails(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andReturn(new class
            {
                public function send($mailable)
                {
                    throw new \RuntimeException('SMTP unreachable');
                }
            });

        $response = $this->post(route('contact.submit'), [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
        ]);

        $response->assertSessionHas('contact_success', true);
        $this->assertSame(1, ContactSubmission::query()->count());
    }
}
