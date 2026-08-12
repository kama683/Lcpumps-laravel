{{--
    Ported from components/admin/ImageUploadField.tsx, minus the
    client-side canvas downscale — the server already caps at 8 MB and
    validates type/signature (Admin\ProductController::uploadImage),
    downscaling is a bandwidth nicety, not correctness, and skipping it
    keeps this a plain <script>, no build step of its own.
    Uploads immediately via fetch (same two-step flow as the source: image
    goes up first, the returned URL rides along with the rest of the form
    on Save) and writes the result into #product-image-url, which the form
    submits as `image`.
--}}
@php
    $fieldId = 'product-image-'.\Illuminate\Support\Str::random(8);
@endphp
<div
    id="{{ $fieldId }}"
    class="rounded-xl border-2 border-dashed border-border-mid bg-surface p-4 text-center"
>
    <div class="image-preview {{ $image ? '' : 'hidden' }} mx-auto mb-3 aspect-4/3 w-full max-w-60 overflow-hidden rounded-lg border border-border-mid bg-white">
        <img src="{{ $image }}" alt="" class="size-full object-contain">
    </div>

    <button type="button" class="upload-trigger inline-flex items-center gap-1.5 border-none bg-transparent text-sm font-semibold text-primary hover:text-primary-dark">
        <span class="label">{{ $image ? 'Заменить изображение' : 'Загрузить изображение' }}</span>
    </button>

    <p class="upload-error mt-2 hidden text-xs font-semibold text-error"></p>

    <input type="hidden" id="product-image-url" name="image" value="{{ $image }}">
    <input type="file" class="file-input sr-only" accept="image/jpeg,image/png,image/webp">
</div>

<script>
(function () {
    const root = document.getElementById(@json($fieldId));
    const preview = root.querySelector('.image-preview');
    const previewImg = preview.querySelector('img');
    const trigger = root.querySelector('.upload-trigger');
    const label = root.querySelector('.label');
    const errorEl = root.querySelector('.upload-error');
    const fileInput = root.querySelector('.file-input');
    const urlInput = document.getElementById('product-image-url');

    trigger.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;

        errorEl.classList.add('hidden');
        label.textContent = 'Загрузка…';
        trigger.disabled = true;

        try {
            const slugField = document.getElementById('slug-field');
            const slug = (slugField ? slugField.value : @json($slug)) || 'unassigned';

            const body = new FormData();
            body.set('file', file);
            body.set('slug', slug);

            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch(@json(route('admin.products.image.upload')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                body,
            });

            const payload = await res.json().catch(() => null);
            if (!res.ok) {
                throw new Error((payload && payload.error) || 'Не удалось загрузить изображение.');
            }

            urlInput.value = payload.url;
            previewImg.src = payload.url;
            preview.classList.remove('hidden');
        } catch (err) {
            errorEl.textContent = err instanceof Error ? err.message : 'Не удалось загрузить изображение.';
            errorEl.classList.remove('hidden');
        } finally {
            label.textContent = urlInput.value ? 'Заменить изображение' : 'Загрузить изображение';
            trigger.disabled = false;
            fileInput.value = '';
        }
    });
})();
</script>
