export function initAccordion() {
    document.querySelectorAll('[data-accordion-item]').forEach((details) => {
        const summary = details.querySelector('[data-accordion-summary]');
        const panel = details.querySelector('[data-accordion-panel]');
        if (!summary || !panel) return;

        summary.addEventListener('click', (event) => {
            event.preventDefault();

            if (details.hasAttribute('open')) {
                panel.style.gridTemplateRows = '0fr';
            } else {
                details.setAttribute('open', '');
                requestAnimationFrame(() => {
                    panel.style.gridTemplateRows = '1fr';
                });
            }
        });

        panel.addEventListener('transitionend', (event) => {
            if (event.propertyName !== 'grid-template-rows') return;
            if (panel.style.gridTemplateRows === '0fr') {
                details.removeAttribute('open');
            }
        });
    });
}
