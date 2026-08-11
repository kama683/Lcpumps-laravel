// The dropdown itself is a native <details>/<summary> (works with zero JS);
// this only adds the outside-click/Escape-to-close polish the source's
// LanguageDropdown had via pointerdown/keydown listeners.
export function initLanguageSwitcher() {
    document.querySelectorAll('[data-language-dropdown]').forEach((details) => {
        document.addEventListener('pointerdown', (e) => {
            if (details.open && !details.contains(e.target)) details.open = false;
        });
        details.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') details.open = false;
        });
    });
}
