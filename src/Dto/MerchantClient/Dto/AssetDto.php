<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class AssetDto
{
    public function __construct(
        public readonly string $identity,
        public readonly string $amount,
        public readonly string $amountUsd,
    ) {
    }
}
