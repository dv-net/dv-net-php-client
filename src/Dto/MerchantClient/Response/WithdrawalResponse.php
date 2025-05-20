<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DateTimeImmutable;

class WithdrawalResponse
{
    public function __construct(
        public readonly string $addressFrom,
        public readonly string $addressTo,
        public readonly string $amount,
        public readonly string $amountUsd,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $currencyId,
        public readonly string $id,
        public readonly string $storeId,
        public readonly ?string $transferId
    ) {
    }
}
