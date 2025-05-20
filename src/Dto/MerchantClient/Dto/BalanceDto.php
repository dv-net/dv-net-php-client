<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class BalanceDto
{
    public function __construct(
        public readonly string $nativeToken,
        public readonly string $nativeTokenUsd,
    ) {
    }
}
