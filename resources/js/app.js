import { initReveal } from './reveal';
import { initImageFade } from './image-fade';
import { initAutoFrame } from './auto-frame';
import { initStatCountUp } from './stat-count-up';
import { initHeader } from './header';
import { initLanguageSwitcher } from './language-switcher';
import { initHeroSlider } from './hero-slider';
import { initAboutVideo } from './about-video';
import { initCatalogPanel } from './catalog-panel';
import { initCatalogSearch } from './catalog-search';
import { initTilt } from './tilt';
import { initProductDownload } from './product-download';
import { initAccordion } from './accordion';
import { initContactForm } from './contact-form';

function boot() {
    initHeader();
    initLanguageSwitcher();
    initReveal();
    initImageFade();
    initAutoFrame();
    initStatCountUp();
    initHeroSlider();
    initAboutVideo();
    initCatalogPanel();
    initCatalogSearch();
    initTilt();
    initProductDownload();
    initAccordion();
    initContactForm();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
