{{-- Ported from app/admin/(protected)/requests/page.tsx + components/admin/RequestCard.tsx. Adds the status filter mentioned in the Stage 8 brief (not present in the source, which lists everything). --}}
<x-layouts.admin title="LCPumps Admin — Заявки">
    <h1 class="font-heading text-[28px] font-bold text-heading">Заявки</h1>
    <p class="mt-2 text-muted">Все заявки, отправленные через форму на странице «Контакты».</p>

    <div class="mt-5 flex gap-2">
        <a href="{{ route('admin.requests.index') }}" class="rounded-pill px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide no-underline {{ $status === null ? 'bg-pill-blue-bg text-primary' : 'bg-white text-muted border border-border-mid' }}">Все</a>
        <a href="{{ route('admin.requests.index', ['status' => 'new']) }}" class="rounded-pill px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide no-underline {{ $status === 'new' ? 'bg-pill-blue-bg text-primary' : 'bg-white text-muted border border-border-mid' }}">Новые</a>
        <a href="{{ route('admin.requests.index', ['status' => 'read']) }}" class="rounded-pill px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide no-underline {{ $status === 'read' ? 'bg-pill-blue-bg text-primary' : 'bg-white text-muted border border-border-mid' }}">Прочитанные</a>
    </div>

    @if ($submissions->isEmpty())
        <div class="mt-10 flex flex-col items-center rounded-2xl border border-dashed border-border-mid bg-white py-16 text-center">
            <p class="mt-3 text-muted">Пока нет ни одной заявки.</p>
        </div>
    @else
        <div class="mt-8 grid grid-cols-1 gap-4 tablet:grid-cols-2">
            @foreach ($submissions as $submission)
                <a
                    href="{{ route('admin.requests.show', ['id' => $submission->id]) }}"
                    class="group relative block rounded-2xl border border-border bg-white p-5 no-underline transition-[transform,border-color,box-shadow] duration-200 hover:-translate-y-0.5 hover:border-[#f2d8db] hover:shadow-card-sm"
                >
                    @if ($submission->status === 'new')
                        <span class="absolute right-4 top-4 size-2.5 rounded-full bg-primary"></span>
                    @endif

                    <div class="flex items-start justify-between gap-3 pr-6">
                        <div class="min-w-0">
                            <div class="truncate font-heading text-base font-bold text-heading">{{ $submission->name ?: '—' }}</div>
                            <div class="mt-0.5 truncate text-sm text-muted">{{ $submission->company ?: '—' }}</div>
                        </div>
                        <span class="shrink-0 rounded-pill bg-surface-alt px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-primary">
                            {{ $submission->mode === 'technical' ? 'Техническая' : 'Обычная' }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1.5 text-[13px] text-muted">
                        <span>{{ $submission->email ?: '—' }}</span>
                        <span>{{ $submission->phone ?: '—' }}</span>
                        @if ($submission->attachments_count > 0)
                            <span>📎 {{ $submission->attachments_count }}</span>
                        @endif
                    </div>

                    <div class="mt-3 text-[12px] text-subtle">{{ $submission->created_at?->format('d.m.Y H:i') }}</div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
