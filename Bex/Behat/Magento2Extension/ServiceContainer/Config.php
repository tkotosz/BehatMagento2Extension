<?php

namespace Bex\Behat\Magento2Extension\ServiceContainer;

class Config
{
    const CONFIG_KEY_MAGENTO_BOOTSTRAP_PATH = 'bootstrap_path';
    const CONFIG_KEY_MAGENTO_APPLICATION_CLASS = 'application_class';
    const CONFIG_KEY_SERVICES = 'services';

    /**
     * @var string
     */
    private $magentoBootstrapPath;

    /**
     * @var string
     */
    private $magentoApplicationClass;

    /**
     * @var string
     */
    private $servicesPath;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->magentoBootstrapPath = $config[self::CONFIG_KEY_MAGENTO_BOOTSTRAP_PATH];
        $this->magentoApplicationClass = $config[self::CONFIG_KEY_MAGENTO_APPLICATION_CLASS];
        $this->servicesPath = $config[self::CONFIG_KEY_SERVICES];
    }

    public function getMagentoBootstrapPath(): string
    {
        return $this->magentoBootstrapPath;
    }

    public function getMagentoApplicationClass(): string
    {
        return $this->magentoApplicationClass;
    }

    public function getServicesPath(): ?string
    {
        return $this->servicesPath;
    }
}
