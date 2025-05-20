<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class AccountDto
{
    public function __construct(
        public string $balance,
        public string $balanceUsd,
        public int $count,
        public int $countWithBalance,
        public CurrencyShortDto $currency
    ) {
    }
}
