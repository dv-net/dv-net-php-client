<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class BlockchainDto
{
    /**
     * @param string[] $currencies
     * @param string[] $tokens
     */
    public function __construct(
        public readonly string $name,
        public readonly IconDto $icon,
        public readonly array $currencies,
        public readonly array $tokens,
    ) {
    }
}
