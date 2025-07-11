<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Response;

use DateTimeImmutable;
use DvNet\DvNetClient\Dto\MerchantClient\Dto\AddressDto;

class ExternalAddressesResponse
{
    /**
     * @param AddressDto[] $address
     * @param string[] $rates
     */
    public function __construct(
        public readonly array $address,
        public readonly DateTimeImmutable $createdAt,
        public readonly string $id,
        public readonly string $payUrl,
        public readonly string $storeExternalId,
        public readonly string $storeId,
        public readonly DateTimeImmutable $updatedAt,
        public readonly array $rates,
        public readonly string $amountUsd,
    ) {
    }
}
