<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\ServiceContainer;

interface ConfigInterface
{
    public const CONFIG_KEY_MAGENTO_BOOTSTRAP = 'bootstrap';

    public const CONFIG_KEY_SERVICES = 'services';

    public function getMagentoBootstrapPath(): string;

    public function getServicesPath(): ?string;
}
