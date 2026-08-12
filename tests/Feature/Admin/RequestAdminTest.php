<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\ContactSubmission;
use App\Models\SubmissionAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RequestAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function actingAsAdmin(): AdminUser
    {
        $admin = AdminUser::create(['username' => 'admin', 'password_hash' => Hash::make('secret123')]);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function makeSubmission(array $overrides = []): ContactSubmission
    {
        return ContactSubmission::create(array_replace([
            'mode' => 'basic',
            'name' => 'Иван Петров',
            'company' => 'ТОО Ромашка',
            'email' => 'ivan@example.com',
            'phone' => '+7 700 000 00 00',
            'message' => 'Нужен насос',
            'status' => 'new',
        ], $overrides));
    }

    public function test_list_shows_all_requests_by_default(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['status' => 'new']);
        $this->makeSubmission(['status' => 'read']);

        $response = $this->get(route('admin.requests.index'));

        $response->assertOk();
        $response->assertSee('Иван Петров');
    }

    public function test_list_filters_by_status(): void
    {
        $this->actingAsAdmin();
        $new = $this->makeSubmission(['name' => 'Новая заявка', 'status' => 'new']);
        $read = $this->makeSubmission(['name' => 'Прочитанная заявка', 'status' => 'read']);

        $response = $this->get(route('admin.requests.index', ['status' => 'new']));

        $response->assertSee('Новая заявка');
        $response->assertDontSee('Прочитанная заявка');
    }

    public function test_opening_a_new_request_marks_it_read(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission(['status' => 'new']);

        $this->get(route('admin.requests.show', ['id' => $submission->id]))->assertOk();

        $this->assertSame('read', $submission->fresh()->status);
    }

    public function test_technical_fields_render_in_canonical_order_on_the_detail_page(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission([
            'mode' => 'technical',
            // Reversed relative to PRIMARY_TECH_KEYS (model before pump_material).
            'tech_primary' => ['pump_material' => 'Чугун', 'model' => 'LC-100'],
            'tech_extra' => null,
        ]);

        $html = $this->get(route('admin.requests.show', ['id' => $submission->id]))->getContent();

        $modelPos = mb_strpos($html, 'Модель');
        $materialPos = mb_strpos($html, 'Материал насоса');
        $this->assertNotFalse($modelPos);
        $this->assertNotFalse($materialPos);
        $this->assertLessThan($materialPos, $modelPos);
    }

    public function test_nonexistent_request_returns_404(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.requests.show', ['id' => (string) Str::uuid()]))->assertStatus(404);
    }

    public function test_deleting_a_request_removes_attachment_files_from_disk(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();
        Storage::disk('local')->put('private-uploads/keep-me.pdf', 'irrelevant control file');
        Storage::disk('local')->put('private-uploads/delete-me.pdf', 'content');
        $attachment = SubmissionAttachment::create([
            'submission_id' => $submission->id,
            'file_name' => 'spec.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 7,
            'storage_path' => 'private-uploads/delete-me.pdf',
        ]);

        $response = $this->delete(route('admin.requests.destroy', ['id' => $submission->id]));

        $response->assertRedirect(route('admin.requests.index'));
        $this->assertNull(ContactSubmission::query()->find($submission->id));
        Storage::disk('local')->assertMissing('private-uploads/delete-me.pdf');
        Storage::disk('local')->assertExists('private-uploads/keep-me.pdf');
    }

    public function test_attachment_downloads_with_correct_headers_and_cyrillic_filename(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();
        Storage::disk('local')->put('private-uploads/file.pdf', 'pdf content here');
        $attachment = SubmissionAttachment::create([
            'submission_id' => $submission->id,
            'file_name' => 'спецификация насоса.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 17,
            'storage_path' => 'private-uploads/file.pdf',
        ]);

        $response = $this->get(route('admin.attachments.download', ['id' => $attachment->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString("filename*=utf-8''".rawurlencode('спецификация насоса.pdf'), $disposition);
    }

    public function test_attachment_download_returns_404_json_when_db_row_missing(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson(route('admin.attachments.download', ['id' => (string) Str::uuid()]));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Not found']);
    }

    public function test_attachment_download_returns_404_json_when_file_missing_on_disk(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();
        $attachment = SubmissionAttachment::create([
            'submission_id' => $submission->id,
            'file_name' => 'gone.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 5,
            'storage_path' => 'private-uploads/does-not-exist.pdf',
        ]);

        $response = $this->getJson(route('admin.attachments.download', ['id' => $attachment->id]));

        $response->assertStatus(404);
        $response->assertJson(['error' => 'File missing on disk']);
    }

    public function test_request_admin_routes_require_authentication(): void
    {
        $submission = $this->makeSubmission();

        $this->get(route('admin.requests.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.requests.show', ['id' => $submission->id]))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.requests.destroy', ['id' => $submission->id]))->assertRedirect(route('admin.login'));
        $this->getJson(route('admin.attachments.download', ['id' => (string) Str::uuid()]))->assertStatus(401);
    }
}
