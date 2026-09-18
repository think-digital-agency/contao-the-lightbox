<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\Tests\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ThinkDigital\ContaoTheLightbox\EventListener\RegisterLightboxAssetsListener;

/**
 * @covers \ThinkDigital\ContaoTheLightbox\EventListener\RegisterLightboxAssetsListener
 */
final class RegisterLightboxAssetsListenerTest extends TestCase
{
    protected function setUp(): void
    {
        unset($GLOBALS['TL_CSS'], $GLOBALS['TL_JAVASCRIPT']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_CSS'], $GLOBALS['TL_JAVASCRIPT']);
    }

    public function testRegistersAssetsOnFrontendMainRequests(): void
    {
        $this->dispatch(isFrontendMainRequest: true);

        $this->assertSame(
            'bundles/contaothelightbox/css/glightbox.min.css|static',
            $GLOBALS['TL_CSS']['contao-the-lightbox'] ?? null,
        );
        $this->assertSame(
            'bundles/contaothelightbox/js/glightbox.min.js|static',
            $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-lib'] ?? null,
        );
        $this->assertSame(
            'bundles/contaothelightbox/js/lightbox.js|static',
            $GLOBALS['TL_JAVASCRIPT']['contao-the-lightbox-init'] ?? null,
        );
    }

    public function testDoesNothingWhenNotAFrontendMainRequest(): void
    {
        $this->dispatch(isFrontendMainRequest: false);

        $this->assertArrayNotHasKey('contao-the-lightbox', $GLOBALS['TL_CSS'] ?? []);
        $this->assertArrayNotHasKey('contao-the-lightbox-lib', $GLOBALS['TL_JAVASCRIPT'] ?? []);
        $this->assertArrayNotHasKey('contao-the-lightbox-init', $GLOBALS['TL_JAVASCRIPT'] ?? []);
    }

    private function dispatch(bool $isFrontendMainRequest): void
    {
        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher->method('isFrontendMainRequest')->willReturn($isFrontendMainRequest);

        $request = Request::create('http://example.com/page');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        (new RegisterLightboxAssetsListener($scopeMatcher))($event);
    }
}
