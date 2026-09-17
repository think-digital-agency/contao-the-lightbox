<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injects the GLightbox stylesheet before `</head>` and the library + init
 * script before `</body>` on every front end HTML response.
 *
 * Replaces the `generatePage` hook this bundle used before (see
 * docs/DECISIONS.md ADR-002, superseded by ADR-007): Contao's Twig-based
 * "content composition" (slot) layouts render through
 * `RegularPageController` and never instantiate the legacy `PageRegular`
 * class, so `generatePage` simply never fires for them — the assets silently
 * vanished, GLightbox included (reported in #1). Post-processing the final
 * Response instead works for both rendering paths, because every front end
 * page produces one regardless of which controller built it.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -300)]
class InjectLightboxAssetsListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ('frontend' !== $request->attributes->get('_scope')) {
            return;
        }

        $response = $event->getResponse();

        if (!str_contains((string) $response->headers->get('Content-Type', ''), 'text/html')) {
            return;
        }

        $content = $response->getContent();

        if (false === $content || !str_contains($content, '</head>') || !str_contains($content, '</body>')) {
            return;
        }

        $base = rtrim($request->getBasePath(), '/');

        $content = str_replace(
            '</head>',
            \sprintf('<link rel="stylesheet" href="%s/bundles/contaothelightbox/css/glightbox.min.css"></head>', $base),
            $content,
        );

        $content = str_replace(
            '</body>',
            \sprintf(
                '<script src="%s/bundles/contaothelightbox/js/glightbox.min.js"></script>'
                .'<script src="%s/bundles/contaothelightbox/js/lightbox.js"></script></body>',
                $base,
                $base,
            ),
            $content,
        );

        $response->setContent($content);
    }
}
