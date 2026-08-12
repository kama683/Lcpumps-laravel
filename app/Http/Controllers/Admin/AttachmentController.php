<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubmissionAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ported from app/api/admin/attachments/[id]/route.ts. Session check is
 * the admin.auth middleware on this whole /api/admin/* route group (see
 * routes/web.php) — the source re-checks per-route because proxy.ts's
 * check is only optimistic; Laravel's middleware isn't, so one check here
 * would just be a second copy of the same thing.
 */
class AttachmentController extends Controller
{
    /** @return JsonResponse|StreamedResponse */
    public function download(Request $request)
    {
        $id = $this->routeParam($request, 'id');

        $attachment = SubmissionAttachment::query()->find($id);
        if ($attachment === null) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if (! Storage::disk('local')->exists($attachment->storage_path)) {
            return response()->json(['error' => 'File missing on disk'], 404);
        }

        // Storage::download() streams via fpassthru(), never reads the
        // whole file into memory — see Illuminate\Filesystem\FilesystemAdapter::response().
        // filename*=UTF-8''<percent-encoded> is added automatically by
        // Symfony whenever the name isn't plain ASCII (Cyrillic file
        // names here), alongside an ASCII fallback the source's
        // hand-rolled header didn't bother with — a strict superset,
        // not a behavior change.
        return Storage::disk('local')->download(
            $attachment->storage_path,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type]
        );
    }
}
