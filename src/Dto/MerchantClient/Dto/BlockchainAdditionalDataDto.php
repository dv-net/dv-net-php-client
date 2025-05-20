<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class BlockchainAdditionalDataDto
{
    public function __construct(
        public readonly TronDataDto $tronData,
    ) {
    }
}
