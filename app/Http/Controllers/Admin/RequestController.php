<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Services\ContactSubmissionHandler;
use App\Services\PrivateAttachmentStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Ported from app/admin/(protected)/requests/{page.tsx,[id]/page.tsx,actions.ts} + lib/repositories/submissions.ts. */
class RequestController extends Controller
{
    private const array PRIMARY_LABELS = [
        'model' => 'Модель',
        'flow' => 'Расход (м³/ч)',
        'head' => 'Напор (м)',
        'power' => 'Мощность (кВт)',
        'speed' => 'Частота вращения (об/мин)',
        'efficiency' => 'КПД (%)',
        'temperature' => 'Рабочая температура (°C)',
        'inlet_diameter' => 'Диаметр всасывания (мм)',
        'outlet_diameter' => 'Диаметр нагнетания (мм)',
        'pump_material' => 'Материал насоса',
    ];

    private const array EXTRA_LABELS = [
        'motor' => 'Двигатель',
        'voltage' => 'Напряжение (В)',
        'protection_level' => 'Степень защиты',
        'cooling_method' => 'Способ охлаждения',
        'motor_efficiency' => 'КПД двигателя (%)',
        'weight' => 'Масса (кг)',
    ];

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, ['new', 'read'], true) ? $status : null;

        $submissions = ContactSubmission::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount('attachments')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.requests.index', [
            'submissions' => $submissions,
            'status' => $status,
        ]);
    }

    public function show(Request $request): View
    {
        $id = $this->routeParam($request, 'id');
        $submission = ContactSubmission::query()->with('attachments')->find($id);
        abort_if($submission === null, 404);

        if ($submission->status === 'new') {
            $submission->update(['status' => 'read']);
        }

        $orderedFields = [];
        if ($submission->mode === 'technical') {
            foreach (ContactSubmissionHandler::PRIMARY_TECH_KEYS as $key) {
                if (array_key_exists($key, $submission->tech_primary ?? [])) {
                    $orderedFields[] = ['label' => self::PRIMARY_LABELS[$key] ?? $key, 'value' => $submission->tech_primary[$key]];
                }
            }
            foreach (ContactSubmissionHandler::EXTRA_TECH_KEYS as $key) {
                if (array_key_exists($key, $submission->tech_extra ?? [])) {
                    $orderedFields[] = ['label' => self::EXTRA_LABELS[$key] ?? $key, 'value' => $submission->tech_extra[$key]];
                }
            }
        }

        return view('admin.requests.show', [
            'submission' => $submission,
            'orderedFields' => $orderedFields,
        ]);
    }

    public function destroy(Request $request, PrivateAttachmentStorage $storage): RedirectResponse
    {
        $id = $this->routeParam($request, 'id');
        $submission = ContactSubmission::query()->with('attachments')->find($id);
        abort_if($submission === null, 404);

        // Storage paths must be read before the row (and its FK-cascaded
        // attachment rows) are gone — the cascade in the DB doesn't touch
        // the disk, see PrivateAttachmentStorage's doc block.
        $storagePaths = $submission->attachments->pluck('storage_path')->all();

        $submission->delete();

        foreach ($storagePaths as $path) {
            $storage->delete($path);
        }

        return redirect()->route('admin.requests.index')->with('admin_success', 'Заявка удалена.');
    }
}
