<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return $this->stub('contact.index');
    }

    // POST submit handler (validation, attachments, notification email,
    // rate limiting) is Stage 6 — not stubbed here.
}
