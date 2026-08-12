<?php

namespace App\Http\Controllers;

use App\Services\ContactSubmissionHandler;
use App\Support\Content;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Whitelist for the ?contactFormError= query param — see submit()'s
     * doc block. Must stay in sync with lang/*\/Contact.php's form.errors.*
     * keys and bootstrap/app.php's PostTooLargeException handler.
     */
    private const array ERROR_CODES = [
        'missingFields', 'invalidEmail', 'invalidFileType',
        'fileTooLarge', 'totalSizeExceeded', 'rateLimited', 'unknown',
    ];

    public function index(): View
    {
        $queryErrorCode = request()->query('contactFormError');
        $errorMessage = session()->has('errors') && session('errors')->has('contact')
            ? session('errors')->first('contact')
            : (in_array($queryErrorCode, self::ERROR_CODES, true)
                ? __('Contact.form.errors.'.$queryErrorCode)
                : null);

        return view('pages.contact.index', [
            'metaTitle' => __('PageMetadata.contact.title'),
            // Bug preserved as-is, matches source's getFaqItems().slice(0, 3)
            // called with no locale arg — see KNOWN_DEFECTS.md #2.
            'faqItems' => array_slice(Content::faqItems(), 0, 3),
            'successMessage' => session('contact_success') === true,
            'errorMessage' => $errorMessage,
            'techOpen' => old('mode') === 'technical',
            'breadcrumb' => [
                ['label' => __('Common.home'), 'route' => 'home'],
                ['label' => __('Nav.contacts')],
            ],
        ]);
    }

    /**
     * Redirect-back contract for the Blade form (Stage 5):
     *  - success: session('contact_success') === true
     *  - error: standard $errors bag, key "contact" (@error('contact') ... @enderror),
     *    already-translated via lang/*\/Contact.php form.errors.*
     *  - old('name'), old('email'), old('company'), old('phone'), old('message'),
     *    old('mode'), and old() for every technical field name below are all flashed
     *    for redisplay (attachments excluded — files can't survive a redirect, the
     *    visitor must reattach them)
     *  - SECOND, rarer error path: if the whole request exceeds php.ini's
     *    post_max_size, PHP drops $_POST/$_FILES before this action (or any
     *    session/CSRF middleware) ever runs — see bootstrap/app.php's
     *    PostTooLargeException render callback. That path can't use the
     *    session, so it redirects with ?contactFormError=totalSizeExceeded
     *    instead — a bare error CODE, always exactly one of the keys under
     *    Contact.form.errors.* (missingFields, invalidEmail,
     *    invalidFileType, fileTooLarge, totalSizeExceeded, rateLimited,
     *    unknown). The Blade page MUST treat this query value as untrusted
     *    input, not as text to display: check it against that whitelist
     *    (anyone can visit /contact?contactFormError=anything directly, no
     *    upload required) and only then render
     *    __('Contact.form.errors.'.$code) — never echo
     *    request()->query('contactFormError') itself. In this path old()
     *    input is lost too — inherent to PHP, not something the
     *    controller can prevent.
     *
     * Field name contract (must match the Blade form exactly):
     *  - honeypot: website (hidden; non-empty ⇒ silently faked success)
     *  - core: name, email (required), company, phone, message (optional)
     *  - mode: mode = "basic" | "technical" (anything else ⇒ treated as basic)
     *  - technical/primary: model, flow, head, power, speed, efficiency,
     *    temperature, inlet_diameter, outlet_diameter, pump_material
     *  - technical/extra: motor, voltage, protection_level, cooling_method,
     *    motor_efficiency, weight
     *  - attachments: attachments[] — MUST have the "[]" suffix. PHP's
     *    multipart parser only produces an array in $_FILES when the field
     *    name ends in "[]"; the source's bare "attachments" relied on
     *    browser FormData semantics that don't carry over to PHP.
     *  - route: POST route('contact.submit') (and loc.contact.submit for
     *    kk/en/zh) — @csrf required, standard Laravel session CSRF.
     */
    public function submit(Request $request, ContactSubmissionHandler $handler): RedirectResponse
    {
        $result = $handler->handle($request);

        if ($result['status'] === 'success') {
            return redirect()->route('contact.index')->with('contact_success', true);
        }

        $message = __('Contact.form.errors.'.$result['errorCode'], ['detail' => $result['errorDetail'] ?? '']);

        return redirect()->route('contact.index')
            ->withInput($request->except('attachments'))
            ->withErrors(['contact' => $message]);
    }
}
