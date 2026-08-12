// Port of components/products/ProductDownloadButton.tsx's handleDownload.
// html2canvas/jspdf are dynamically imported so they only load (as a
// separate chunk) when a visitor actually clicks download, matching the
// source's code-split behavior.
const PAGE_WIDTH_MM = 210;
const PAGE_HEIGHT_MM = 297;
const MARGIN_MM = 12;

export function initProductDownload() {
    document.querySelectorAll('[data-product-download]').forEach((button) => {
        const icon = button.querySelector('[data-download-icon]');
        const spinner = button.querySelector('[data-download-spinner]');
        const label = button.querySelector('[data-download-label]');

        button.addEventListener('click', async () => {
            if (button.disabled) return;
            button.disabled = true;
            icon.classList.add('hidden');
            spinner.classList.remove('hidden');
            label.textContent = button.dataset.downloadingLabel;

            try {
                const captureRoot = document.querySelector('[data-product-capture]');
                const img = captureRoot.querySelector('img');
                if (img && !img.complete) {
                    await new Promise((resolve) => {
                        img.addEventListener('load', () => resolve(), { once: true });
                        img.addEventListener('error', () => resolve(), { once: true });
                    });
                }
                await document.fonts.ready;

                const [{ default: html2canvas }, { jsPDF }] = await Promise.all([
                    import('html2canvas'),
                    import('jspdf'),
                ]);

                const canvas = await html2canvas(captureRoot, { scale: 2, backgroundColor: '#ffffff' });

                const contentWidthMM = PAGE_WIDTH_MM - MARGIN_MM * 2;
                const contentHeightMM = PAGE_HEIGHT_MM - MARGIN_MM * 2;
                const imgWidthMM = contentWidthMM;
                const imgHeightMM = (canvas.height * imgWidthMM) / canvas.width;
                // JPEG, not PNG: jsPDF embeds PNG canvas data as an uncompressed
                // raw bitmap; JPEG goes through DCTDecode and stays well under
                // 1MB at this quality with no visible loss on this content.
                const imgData = canvas.toDataURL('image/jpeg', 0.92);

                const pdf = new jsPDF('p', 'mm', 'a4');
                let heightLeft = imgHeightMM;
                let position = MARGIN_MM;

                pdf.addImage(imgData, 'JPEG', MARGIN_MM, position, imgWidthMM, imgHeightMM);
                heightLeft -= contentHeightMM;

                while (heightLeft > 0) {
                    position = MARGIN_MM - (imgHeightMM - heightLeft);
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', MARGIN_MM, position, imgWidthMM, imgHeightMM);
                    heightLeft -= contentHeightMM;
                }

                pdf.save(button.dataset.filename);
            } finally {
                button.disabled = false;
                icon.classList.remove('hidden');
                spinner.classList.add('hidden');
                label.textContent = button.dataset.label;
            }
        });
    });
}
