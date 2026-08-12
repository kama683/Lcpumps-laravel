<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Stage 8 placeholder (private attachment download) — route exists now, behind admin.auth. */
class AttachmentController extends Controller
{
    public function download(Request $request): JsonResponse
    {
        return $this->stubJson('admin.attachments.download', ['id' => $this->routeParam($request, 'id')]);
    }
}
