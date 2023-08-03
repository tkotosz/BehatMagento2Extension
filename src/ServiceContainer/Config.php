<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\ServiceContainer;

final class Config implements ConfigInterface
{
    private string $magentoBootstrapPath;

    private ?string $servicesPath;

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
