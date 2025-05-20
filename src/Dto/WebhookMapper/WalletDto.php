<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\WebhookMapper;

class WalletDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $storeExternalId,
    ) {
    }
}
