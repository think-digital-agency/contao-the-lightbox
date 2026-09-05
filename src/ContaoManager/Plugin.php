<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ThinkDigital\ContaoTheLightbox\ContaoTheLightboxBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoTheLightboxBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
