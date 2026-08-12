{{-- Ported from app/admin/(protected)/requests/[id]/page.tsx + components/admin/{AttachmentRow,DeleteRequestButton}.tsx. --}}
@php
    $field = fn (string $label, ?string $value) => ['label' => $label, 'value' => $value];
@endphp
<x-layouts.admin title="LCPumps Admin — Заявка">
    <a href="{{ route('admin.requests.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary no-underline">
        &larr; Назад к заявкам
    </a>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-heading text-[26px] font-bold text-heading">{{ $submission->name ?: 'Без имени' }}</h1>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 rounded-pill bg-pill-blue-bg px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide text-primary">
                {{ $submission->created_at?->format('d.m.Y H:i') }}
            </span>
            <form method="POST" action="{{ route('admin.requests.destroy', ['id' => $submission->id]) }}" onsubmit="return confirm('Удалить заявку? Это необратимо.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-sm border border-border-mid bg-white px-3.5 py-2 text-[13px] font-bold text-muted transition-colors hover:border-error hover:text-error">
                    Удалить заявку
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 tablet:grid-cols-2">
        <section class="rounded-2xl border border-border bg-white p-6">
            <h2 class="mb-4 font-heading text-base font-bold text-heading">Личные данные</h2>
            <div class="grid grid-cols-1 gap-4 mobile:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Имя</div>
                    <div class="mt-1 text-sm text-body">{{ $submission->name ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Компания</div>
                    <div class="mt-1 text-sm text-body">{{ $submission->company ?: '—' }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-white p-6">
            <h2 class="mb-4 font-heading text-base font-bold text-heading">Контактная информация</h2>
            <div class="grid grid-cols-1 gap-4 mobile:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Email</div>
                    <div class="mt-1 text-sm text-body">{{ $submission->email ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Телефон</div>
                    <div class="mt-1 text-sm text-body">{{ $submission->phone ?: '—' }}</div>
                </div>
            </div>
        </section>
    </div>

    <div class="mt-5">
        <section class="rounded-2xl border border-border bg-white p-6">
            <h2 class="mb-4 font-heading text-base font-bold text-heading">Сообщение</h2>
            <p class="whitespace-pre-wrap text-sm leading-relaxed text-body">{{ $submission->message ?: '—' }}</p>
        </section>
    </div>

    @if ($submission->mode === 'technical')
        <div class="mt-5">
            <section class="rounded-2xl border border-border bg-white p-6">
                <h2 class="mb-4 font-heading text-base font-bold text-heading">Технические характеристики</h2>
                @if (count($orderedFields) === 0)
                    <p class="text-sm text-muted">Не заполнены.</p>
                @else
                    <div class="grid grid-cols-1 gap-4 mobile:grid-cols-2 tablet:grid-cols-3">
                        @foreach ($orderedFields as $f)
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ $f['label'] }}</div>
                                <div class="mt-1 text-sm text-body">{{ $f['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    @endif

    <div class="mt-5">
        <section class="rounded-2xl border border-border bg-white p-6">
            <h2 class="mb-4 font-heading text-base font-bold text-heading">Вложения ({{ $submission->attachments->count() }})</h2>
            @if ($submission->attachments->isEmpty())
                <p class="text-sm text-muted">Файлы не прикреплены.</p>
            @else
                <div class="flex flex-col gap-2.5">
                    @foreach ($submission->attachments as $attachment)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-border-mid bg-white px-4 py-3">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-body">{{ $attachment->file_name }}</div>
                                <div class="text-xs text-subtle">{{ number_format($attachment->size_bytes / 1024, 1) }} KB</div>
                            </div>
                            <a
                                href="{{ route('admin.attachments.download', ['id' => $attachment->id]) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-sm border border-border-mid bg-white px-3.5 py-2 text-[13px] font-bold text-body no-underline transition-colors hover:border-primary hover:text-primary"
                            >
                                Скачать
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.admin>
