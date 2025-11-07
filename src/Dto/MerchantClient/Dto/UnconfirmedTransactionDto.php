<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

use DateTimeImmutable;

class UnconfirmedTransactionDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $txHash,
        public readonly string $toAddress,
        public readonly string $bcUniqKey,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $currencyId,
        public readonly string $blockchain,
        public readonly string $amount,
        public readonly ?string $amountUsd,
        public readonly DateTimeImmutable $networkCreatedAt,
        public readonly string $type,
    ) {
    }
}
