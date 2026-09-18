<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ThinkDigital\ContaoTheLightbox\EventListener\InjectLightboxAssetsListener;

/**
 * @covers \ThinkDigital\ContaoTheLightbox\EventListener\InjectLightboxAssetsListener
 */
final class InjectLightboxAssetsListenerTest extends TestCase
{
    public function testInjectsStylesheetBeforeHeadAndScriptsBeforeBodyOnFrontendHtml(): void
    {
        $response = $this->dispatch(scope: 'frontend', contentType: 'text/html; charset=UTF-8');

        $content = (string) $response->getContent();

        $this->assertStringContainsString(
            '<link rel="stylesheet" href="/bundles/contaothelightbox/css/glightbox.min.css"></head>',
            $content,
        );
        $this->assertStringContainsString(
            '<script src="/bundles/contaothelightbox/js/glightbox.min.js"></script>'
            .'<script src="/bundles/contaothelightbox/js/lightbox.js"></script></body>',
            $content,
        );
    }

    public function testDoesNothingOnBackendScope(): void
    {
        $response = $this->dispatch(scope: 'backend', contentType: 'text/html');

        $this->assertSame($this->page(), $response->getContent());
    }

    public function testDoesNothingWhenScopeAttributeIsMissing(): void
    {
        $response = $this->dispatch(scope: null, contentType: 'text/html');

        $this->assertSame($this->page(), $response->getContent());
    }

    public function testDoesNothingForNonHtmlResponses(): void
    {
        $response = $this->dispatch(scope: 'frontend', contentType: 'application/json');

        $this->assertSame($this->page(), $response->getContent());
    }

    public function testDoesNothingForSubRequests(): void
    {
        $request = Request::create('http://example.com/page');
        $request->attributes->set('_scope', 'frontend');

        $response = new Response($this->page());
        $response->headers->set('Content-Type', 'text/html');

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST, $response);

        (new InjectLightboxAssetsListener())($event);

        $this->assertSame($this->page(), $response->getContent());
    }

    public function testDoesNothingWhenBodyHasNoClosingHeadOrBodyTag(): void
    {
        $request = Request::create('http://example.com/page');
        $request->attributes->set('_scope', 'frontend');

        $response = new Response('{}');
        $response->headers->set('Content-Type', 'text/html');

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        (new InjectLightboxAssetsListener())($event);

        $this->assertSame('{}', $response->getContent());
    }

    private function page(): string
    {
        return '<html><head><title>x</title></head><body><p>hi</p></body></html>';
    }

    private function dispatch(?string $scope, string $contentType): Response
    {
        $request = Request::create('http://example.com/page');

        if (null !== $scope) {
            $request->attributes->set('_scope', $scope);
        }

        $response = new Response($this->page());
        $response->headers->set('Content-Type', $contentType);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);

        (new InjectLightboxAssetsListener())($event);

        return $response;
    }
}
