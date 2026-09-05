<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoTheLightboxBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
