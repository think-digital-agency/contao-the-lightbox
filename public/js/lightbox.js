/*
 * Contao The Lightbox — init script.
 *
 * Ports the inline script of the legacy `js_glightbox` template:
 * mirror every `a[data-lightbox]` link's `data-lightbox` value onto
 * `data-gallery` (a random group when the value is empty), then start
 * GLightbox on the same selector.
 *
 * Loaded from <head> as part of the combined script bundle, so the work
 * is deferred until the DOM is ready.
 */
(function () {
    'use strict';

    function init() {
        if (typeof GLightbox !== 'function') {
            return;
        }

        document.querySelectorAll('a[data-lightbox]').forEach(function (element) {
            if (element.dataset.lightbox) {
                element.setAttribute('data-gallery', element.dataset.lightbox);
            } else {
                element.setAttribute('data-gallery', (Math.random() + 1).toString(36).substring(7));
            }
        });

        GLightbox({
            selector: 'a[data-lightbox]'
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
