<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DvNet\DvNetClient\Dto\MerchantClient\Dto\ProcessingWalletBalanceDto;

class ProcessingWalletsBalancesResponse
{
    /** @param ProcessingWalletBalanceDto[] $balances */
    public function __construct(
        public readonly array $balances,
    ) {
    }
}
