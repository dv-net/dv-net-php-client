<?php

declare(strict_types = 1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class TransferDto
{
    public function __construct(
        public readonly string $kind,
        public readonly string $stage,
        public readonly string $status
    ) {
    }
}
