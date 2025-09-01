<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class CurrencyDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $blockchain,
        public readonly string $code,
        public readonly string $contractAddress,
        public readonly bool $hasBalance,
        public readonly IconDto $icon,
        public readonly IconDto $blockchainIcon,
        public readonly bool $isFiat,
        public readonly int $minConfirmation,
        public readonly string $name,
        public readonly int $precision,
        public readonly bool $status,
        public readonly string $withdrawalMinBalance,
        public readonly string $explorerLink,
    ) {
    }
}
