<?php

namespace Bex\Behat\Magento2Extension\ServiceContainer;

class Config
{
    const CONFIG_KEY_MAGENTO_BOOTSTRAP = 'bootstrap';
    const CONFIG_KEY_SERVICES = 'services';

    private string $magentoBootstrapPath;
    private string $servicesPath;

    public function __construct(array $config)
    {
        $this->magentoBootstrapPath = (string) $config[self::CONFIG_KEY_MAGENTO_BOOTSTRAP];
        $this->servicesPath = (string) $config[self::CONFIG_KEY_SERVICES];
    }

    public function getMagentoBootstrapPath(): string
    {
        return $this->magentoBootstrapPath;
    }

    public function getServicesPath(): ?string
    {
        return $this->servicesPath;
    }
}
