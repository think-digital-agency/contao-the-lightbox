<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

/**
 * Registers the GLightbox stylesheet, the GLightbox library and the small init
 * script on every front end page.
 *
 * This replaces the legacy `js_glightbox` layout script template of
 * inspiredminds/contao-glightbox. That template relied on the front end page
 * template echoing `{{ scripts }}` / `{{ mootools }}`; the Design+ theme's
 * `fe_page.html.twig` does not, so its `<script>` output silently vanished and
 * the lightbox never initialised (only its CSS side effect survived, because
 * that runs during template compilation regardless).
 *
 * Assets added to `$GLOBALS['TL_CSS']` / `$GLOBALS['TL_JAVASCRIPT']` are always
 * injected before `</head>` by Contao's ReplaceDynamicScriptTagsListener, so
 * this path does not depend on the page template at all. See docs/DECISIONS.md
 * ADR-002 / ADR-003.
 *
 * The library self-reports as a global `GLightbox`; the init script is appended
 * right after it (same combined bundle, evaluated in order) and defers its work
 * to DOMContentLoaded because the combined script tag sits in `<head>`.
 */
#[AsHook('generatePage')]
class RegisterLightboxAssetsListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        $GLOBALS['TL_CSS']['contao-the-lightbox'] = 'bundles/contaothelightbox/css/glightbox.min.css|static';

        $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-lib'] = 'bundles/contaothelightbox/js/glightbox.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-init'] = 'bundles/contaothelightbox/js/lightbox.js|static';
    }
}
