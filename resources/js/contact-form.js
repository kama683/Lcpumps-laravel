// Port of components/contact/ContactForms.tsx's client state (techOpen,
// dragOver, selectedFiles). Submission itself is a plain HTML form POST
// (see ContactController::submit), not the source's useActionState — no
// client-side pending/success/error state to replicate, the page just
// reloads with the result already rendered server-side.
export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');
    if (!form) return;

    const modeInput = form.querySelector('[data-contact-mode-input]');
    const techCta = form.querySelector('[data-tech-cta]');
    const techPanel = form.querySelector('[data-tech-panel]');
    const techPanelInner = form.querySelector('[data-tech-panel-inner]');
    const openBtn = form.querySelector('[data-tech-open-btn]');
    const collapseBtn = form.querySelector('[data-tech-collapse-btn]');

    function setTechOpen(open) {
        if (modeInput) modeInput.value = open ? 'technical' : 'basic';
        if (techCta) techCta.hidden = open;
        if (techPanel) {
            techPanel.classList.toggle('grid-rows-[1fr]', open);
            techPanel.classList.toggle('grid-rows-[0fr]', !open);
        }
        if (techPanelInner) {
            techPanelInner.classList.toggle('overflow-visible', open);
            techPanelInner.classList.toggle('overflow-clip', !open);
            techPanelInner.classList.toggle('[content-visibility:hidden]', !open);
        }
        openBtn?.setAttribute('aria-expanded', String(open));
        collapseBtn?.setAttribute('aria-expanded', String(open));
    }

    openBtn?.addEventListener('click', () => setTechOpen(true));
    collapseBtn?.addEventListener('click', () => setTechOpen(false));

    // File upload: manual "choose file" + drag & drop, both feed the same
    // hidden <input type="file" name="attachments[]">.
    const dropzone = form.querySelector('[data-upload-dropzone]');
    const fileInput = form.querySelector('[data-upload-input]');
    const fileList = form.querySelector('[data-upload-filelist]');
    const uploadIconWrap = form.querySelector('[data-upload-icon]');

    function renderSelectedFiles(files) {
        const names = Array.from(files).map((f) => f.name);

        if (uploadIconWrap) {
            uploadIconWrap.innerHTML = names.length
                ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-7 animate-contact-fade-in text-success"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>'
                : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-7"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M12 12v6"/><path d="m15 15-3-3-3 3"/></svg>';
        }

        if (fileList) {
            fileList.innerHTML = '';
            if (names.length) {
                fileList.classList.remove('hidden');
                fileList.classList.add('animate-contact-fade-in');
                names.forEach((name) => {
                    const li = document.createElement('li');
                    li.className = 'inline-flex items-center gap-1.5 rounded-pill bg-white border border-[#f2d8db] px-3.5 py-1.5 text-[13px] font-semibold text-body';
                    li.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 shrink-0 text-success"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>';
                    li.append(name);
                    fileList.appendChild(li);
                });
            } else {
                fileList.classList.add('hidden');
            }
        }
    }

    fileInput?.addEventListener('change', () => {
        if (fileInput.files) renderSelectedFiles(fileInput.files);
    });

    if (dropzone && fileInput) {
        dropzone.addEventListener('dragenter', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-primary', 'bg-[#fbeeef]', 'shadow-[0_0_0_4px_rgba(247,6,32,0.1)]');
        });
        dropzone.addEventListener('dragover', (e) => e.preventDefault());
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-primary', 'bg-[#fbeeef]', 'shadow-[0_0_0_4px_rgba(247,6,32,0.1)]');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-primary', 'bg-[#fbeeef]', 'shadow-[0_0_0_4px_rgba(247,6,32,0.1)]');
            const files = e.dataTransfer?.files;
            if (files && files.length) {
                fileInput.files = files;
                renderSelectedFiles(files);
            }
        });
    }
}
