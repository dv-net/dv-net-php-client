<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DvNet\DvNetClient\Dto\MerchantClient\Dto\ExchangeBalanceDto;

class TotalExchangeBalanceResponse
{
    /**
     * @param ExchangeBalanceDto[] $exchangeBalance
     */
    public function __construct(
        public readonly string $totalUsd,
        public readonly array $exchangeBalance,
    ) {
    }
}
