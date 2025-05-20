<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\WebhookMapper;

use DateTimeImmutable;

class TransactionDto
{
    public function __construct(
        public readonly string $txId,
        public readonly string $txHash,
        public readonly string $bcUniqKey,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $currency,
        public readonly string $currencyId,
        public readonly string $blockchain,
        public readonly string $amount,
        public readonly string $amountUsd,
    ) {
    }
}
