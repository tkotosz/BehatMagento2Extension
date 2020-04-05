<?php

namespace Acme\Awesome\Config;

interface ConfigProviderInterface
{
    public function isFreeDeliverEnabled(): bool;

    public function getFreeDeliveryThreshold(): float;
}