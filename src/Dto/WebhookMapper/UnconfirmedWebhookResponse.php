<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\WebhookMapper;

use DateTimeImmutable;

class UnconfirmedWebhookResponse
{
    public function __construct(
        public readonly string $type,
        public readonly string $status,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $paidAt,
        public readonly string $amount,
        public readonly TransactionDto $transactions,
        public readonly WalletDto $wallet,
    ) {
    }
}
