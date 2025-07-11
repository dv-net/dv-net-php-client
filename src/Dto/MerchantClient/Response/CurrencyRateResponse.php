<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

class CurrencyRateResponse
{
    public function __construct(
        public readonly string $code,
        public readonly string $rate,
        public readonly string $rateSource,
    ) {
    }
}
