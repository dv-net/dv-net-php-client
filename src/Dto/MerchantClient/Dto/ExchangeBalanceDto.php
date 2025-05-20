<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class ExchangeBalanceDto
{
    public function __construct(
        public readonly string $amount,
        public readonly string $amountUsd,
        public readonly string $currency,
    ) {
    }
}
