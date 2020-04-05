<?php

namespace Acme\Awesome\Test;

use Acme\Awesome\Config\ConfigProviderInterface;

class FakeConfigProvider implements ConfigProviderInterface
{
    /** @var bool */
    private $isFreeDeliveryEnabled = false;

    /** @var float */
    private $freeDeliveryThreshold = 0.0;

    public function isFreeDeliverEnabled(): bool
    {
        return $this->isFreeDeliveryEnabled;
    }

    public function getFreeDeliveryThreshold(): float
    {
        return $this->freeDeliveryThreshold;
    }

    public function enableFreeDelivery(): void
    {
        $this->isFreeDeliveryEnabled = true;
    }

    public function disableFreeDelivery(): void
    {
        $this->isFreeDeliveryEnabled = false;
    }

    public function setFreeDeliveryThreshold(float $threshold): void
    {
        $this->freeDeliveryThreshold = $threshold;
    }
}