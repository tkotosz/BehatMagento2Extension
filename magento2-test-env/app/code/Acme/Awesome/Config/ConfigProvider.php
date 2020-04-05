<?php

namespace Acme\Awesome\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isFreeDeliverEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('path/to/config');
    }

    public function getFreeDeliveryThreshold(): float
    {
        return (float) $this->scopeConfig->getValue('path/to/another/config');
    }
}