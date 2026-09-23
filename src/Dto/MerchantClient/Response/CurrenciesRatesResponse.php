<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

class CurrenciesRatesResponse
{
    /** @param CurrencyRateResponse[] $rates */
    public function __construct(
        public readonly array $rates,
    ) {
    }
}
