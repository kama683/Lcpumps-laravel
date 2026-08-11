// Port of components/catalog/CatalogLayout.tsx's instant search: filters an
// embedded JSON index (code/name substring match, case-insensitive, capped
// at 8 hits) as the user types, with the same keyboard nav.
export function initCatalogSearch() {
    const wrapper = document.querySelector('[data-catalog-search]');
    if (!wrapper) return;

    const input = wrapper.querySelector('input[type="search"]');
    const listbox = wrapper.querySelector('[data-search-listbox]');
    const index = JSON.parse(document.getElementById('catalog-search-index')?.textContent || '[]');
    const noResultsText = wrapper.dataset.noResultsText || '';
    const productsBaseUrl = wrapper.dataset.productsBaseUrl || '/products';

    let hits = [];
    let highlighted = -1;

    function render() {
        if (!listbox) return;
        if (!input.value.trim()) {
            listbox.innerHTML = '';
            listbox.classList.add('hidden');
            return;
        }
        listbox.classList.remove('hidden');

        if (hits.length === 0) {
            listbox.innerHTML = `<div class="py-2.5 px-1 text-[13px] text-subtle">${noResultsText}</div>`;
            return;
        }

        listbox.innerHTML = hits
            .map((hit, i) => {
                const active = i === highlighted;
                const label = hit.hasModelCode
                    ? `<strong class="text-heading">${hit.code}</strong> — ${hit.name}`
                    : hit.name;
                return `<a href="${productsBaseUrl}/${hit.slug}" role="option" data-index="${i}" class="block py-2.5 px-3 rounded-sm text-sm text-muted leading-snug no-underline hover:bg-[#fcf0f1] hover:text-primary ${active ? 'bg-[#fcf0f1] text-primary' : ''}">${label}</a>`;
            })
            .join('');
    }

    function search() {
        const q = input.value.trim().toLowerCase();
        hits = q
            ? index
                  .filter((p) => p.code.toLowerCase().includes(q) || p.name.toLowerCase().includes(q))
                  .slice(0, 8)
            : [];
        highlighted = -1;
        render();
    }

    input.addEventListener('input', search);

    input.addEventListener('keydown', (e) => {
        if (!hits.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlighted = (highlighted + 1) % hits.length;
            render();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlighted = highlighted <= 0 ? hits.length - 1 : highlighted - 1;
            render();
        } else if (e.key === 'Enter') {
            const hit = hits[highlighted] ?? hits[0];
            if (hit) {
                e.preventDefault();
                window.location.href = `${productsBaseUrl}/${hit.slug}`;
            }
        } else if (e.key === 'Escape') {
            input.value = '';
            search();
        }
    });

    listbox?.addEventListener('mousemove', (e) => {
        const link = e.target.closest('[data-index]');
        if (link) {
            highlighted = Number(link.dataset.index);
        }
    });
}
