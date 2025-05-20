<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

use DateTimeImmutable;

class AddressDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $walletId,
        public readonly string $userId,
        public readonly string $currencyId,
        public readonly string $blockchain,
        public readonly string $address,
        public readonly bool $dirty,
    ) {
    }
}
