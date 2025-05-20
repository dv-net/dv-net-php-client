<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DvNet\DvNetClient\Dto\MerchantClient\Dto\CurrencyDto;

class CurrenciesResponse
{
    /** @param CurrencyDto[] $currencies */
    public function __construct(
        public readonly array $currencies
    ) {
    }
}
