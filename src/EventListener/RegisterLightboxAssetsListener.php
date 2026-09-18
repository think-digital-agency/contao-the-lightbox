<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Registers the GLightbox stylesheet, the GLightbox library and the small init
 * script on every front end page via $GLOBALS['TL_CSS'] / $GLOBALS['TL_JAVASCRIPT'].
 *
 * Supersedes both the `generatePage` hook (ADR-002, superseded by ADR-007) and
 * the `kernel.response` string-replace listener ADR-007 introduced. Contao's
 * ContentCompositionBuilder — the class rendering Twig-based content
 * composition (slot) layouts — reads TL_CSS / TL_JAVASCRIPT directly, the same
 * as the legacy PageRegular path; only the *trigger* that populates them was
 * ever the problem (the generatePage hook only fires from inside
 * PageRegular::generate(), never for slot layouts). A kernel.request listener
 * gated on ScopeMatcher::isFrontendMainRequest() fires for every front end
 * page regardless of which controller renders it, so it works for both paths
 * without reimplementing Contao's own asset injection via string replacement.
 * See docs/DECISIONS.md ADR-008 (h/t @zoglo for the review on #2).
 */
#[AsEventListener(event: KernelEvents::REQUEST)]
class RegisterLightboxAssetsListener
{
    public function __construct(private readonly ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isFrontendMainRequest($event)) {
            return;
        }

        $GLOBALS['TL_CSS']['contao-the-lightbox'] = 'bundles/contaothelightbox/css/glightbox.min.css|static';
        $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-lib'] = 'bundles/contaothelightbox/js/glightbox.min.js|static';
        $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-init'] = 'bundles/contaothelightbox/js/lightbox.js|static';
    }
}
