{{--
    Internal staff notification — not locale-aware (unlike the public form),
    since the recipient (CONTACT_NOTIFY_TO) is always the Russian-speaking
    sales/engineering team. Labels below are re-keyed (snake_case) copies of
    lang/ru/Contact.php's form.primaryFields/extraFields, kept separate so
    this template doesn't depend on the visitor-facing translation namespace.
--}}
@php
    $techLabels = [
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
        'motor' => 'Двигатель',
        'voltage' => 'Напряжение (В)',
        'protection_level' => 'Степень защиты',
        'cooling_method' => 'Способ охлаждения',
        'motor_efficiency' => 'КПД двигателя (%)',
        'weight' => 'Масса (кг)',
    ];
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Новая заявка с сайта</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1a1a1a;">
    <h2 style="margin-bottom: 0;">Новая заявка с сайта</h2>
    <p style="color: #666; margin-top: 4px;">
        {{ $submission->created_at?->format('d.m.Y H:i') }} ·
        Режим: {{ $submission->mode === 'technical' ? 'Технический' : 'Базовый' }}
    </p>

    <table cellpadding="4" cellspacing="0" style="border-collapse: collapse; margin-top: 12px;">
        <tr><td style="color:#666;">Имя</td><td><strong>{{ $submission->name }}</strong></td></tr>
        @if ($submission->company !== '')
            <tr><td style="color:#666;">Компания</td><td>{{ $submission->company }}</td></tr>
        @endif
        <tr><td style="color:#666;">Email</td><td>{{ $submission->email }}</td></tr>
        @if ($submission->phone !== '')
            <tr><td style="color:#666;">Телефон</td><td>{{ $submission->phone }}</td></tr>
        @endif
    </table>

    @if ($submission->message !== '')
        <h3 style="margin-bottom: 4px;">Сообщение</h3>
        <p style="white-space: pre-wrap;">{{ $submission->message }}</p>
    @endif

    @if ($submission->mode === 'technical' && ($submission->tech_primary || $submission->tech_extra))
        @php
            // Keyed by $orderedTechKeys (from NewContactSubmissionMail), not
            // by this merged array's own key order — MySQL's JSON type
            // doesn't preserve insertion order the way sqlite's does.
            $techValues = array_merge($submission->tech_primary ?? [], $submission->tech_extra ?? []);
        @endphp
        <h3 style="margin-bottom: 4px;">Технические параметры</h3>
        <table cellpadding="4" cellspacing="0" style="border-collapse: collapse;">
            @foreach ($orderedTechKeys as $key)
                @continue(! array_key_exists($key, $techValues))
                <tr>
                    <td style="color:#666;">{{ $techLabels[$key] ?? $key }}</td>
                    <td>{{ $techValues[$key] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($submission->attachments->isNotEmpty())
        <h3 style="margin-bottom: 4px;">Вложения ({{ $submission->attachments->count() }})</h3>
        <ul style="margin-top: 0;">
            @foreach ($submission->attachments as $attachment)
                <li>{{ $attachment->file_name }}</li>
            @endforeach
        </ul>
        <p style="color: #666; font-size: 12px;">Файлы хранятся приватно на сервере — откройте заявку в админке, чтобы скачать их.</p>
    @endif

    @if (\Illuminate\Support\Facades\Route::has('admin.requests.show'))
        <p style="margin-top: 20px;">
            <a href="{{ route('admin.requests.show', ['id' => $submission->id]) }}">Открыть заявку в админке</a>
        </p>
    @endif
</body>
</html>
