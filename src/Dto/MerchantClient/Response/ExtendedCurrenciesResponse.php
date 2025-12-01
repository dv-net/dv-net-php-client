<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DvNet\DvNetClient\Dto\MerchantClient\Dto\BlockchainDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\ExtendedCurrencyDto;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\TokenDto;

class ExtendedCurrenciesResponse
{
    /**
     * @param TokenDto[] $tokens
     * @param BlockchainDto[] $blockchains
     * @param ExtendedCurrencyDto[] $currencies
     */
    public function __construct(
        public readonly array $tokens,
        public readonly array $blockchains,
        public readonly array $currencies,
    ) {
    }
}
