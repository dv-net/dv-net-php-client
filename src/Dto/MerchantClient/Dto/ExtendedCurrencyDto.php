<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class ExtendedCurrencyDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly string $blockchain,
        public readonly string $contractAddress,
        public readonly bool $hasBalance,
        public readonly int $minConfirmation,
        public readonly IconDto $icon,
        public readonly IconDto $tokenIcon,
        public readonly IconDto $blockchainIcon,
        public readonly string $explorerLink,
    ) {
    }
}
