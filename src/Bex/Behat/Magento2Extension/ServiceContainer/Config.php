<?php

namespace Bex\Behat\Magento2Extension\ServiceContainer;

class Config
{
    const CONFIG_KEY_MAGENTO_BOOTSTRAP = 'bootstrap';
    const CONFIG_KEY_SERVICES = 'services';

    /**
     * @var string
     */
    private $magentoBootstrapPath;

    /**
     * @var string
     */
    private $servicesPath;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->magentoBootstrapPath = $config[self::CONFIG_KEY_MAGENTO_BOOTSTRAP];
        $this->servicesPath = $config[self::CONFIG_KEY_SERVICES];
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
