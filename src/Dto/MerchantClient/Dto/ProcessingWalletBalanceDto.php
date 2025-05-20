<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class ProcessingWalletBalanceDto
{
    /**
     * @param AssetDto[] $asset
     */
    public function __construct(
        public readonly string                       $address,
        public readonly string                       $blockchain,
        public readonly array                        $asset,
        public readonly CurrencyShortDto             $currency,
        public readonly BalanceDto                   $balance,
        public readonly ?BlockchainAdditionalDataDto $additionalData,
    ) {
    }
}
