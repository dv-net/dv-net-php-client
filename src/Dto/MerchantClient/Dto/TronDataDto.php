<?php

declare(strict_types=1);

namespace DvNet\DvNetClient\Dto\MerchantClient\Dto;

class TronDataDto
{
    public function __construct(
        public readonly string $availableBandwidthForUse,
        public readonly string $availableEnergyForUse,
        public readonly string $stackedBandwidth,
        public readonly string $stackedBandwidthTrx,
        public readonly string $stackedEnergy,
        public readonly string $stackedEnergyTrx,
        public readonly string $stackedTrx,
        public readonly string $totalBandwidth,
        public readonly string $totalEnergy,
        public readonly string $totalUsedBandwidth,
        public readonly string $totalUsedEnergy,
    ) {
    }
}
