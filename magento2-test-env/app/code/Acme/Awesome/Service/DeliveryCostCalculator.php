<?php

namespace Acme\Awesome\Service;

use Acme\Awesome\Config\ConfigProviderInterface;

class DeliveryCostCalculator
{
    private const DELIVERY_COST = 5.0;

    /** @var ConfigProviderInterface */
    private $deliveryConfig;

    public function __construct(ConfigProviderInterface $deliveryConfig)
    {
        $this->deliveryConfig = $deliveryConfig;
    }

    public function calculate(float $total): float
    {
        if ($this->isFreeDelivery($total)) {
            return 0.0;
        }

        return self::DELIVERY_COST;
    }

    private function isFreeDelivery(float $total): bool
    {
        if (!$this->deliveryConfig->isFreeDeliverEnabled()) {
            return false;
        }

        return $total >= $this->deliveryConfig->getFreeDeliveryThreshold();
    }
}