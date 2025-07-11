<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class IconDto
{
    public function __construct(
        public readonly string $icon128,
        public readonly string $icon512,
        public readonly string $iconSvg,
    ) {
    }
}
