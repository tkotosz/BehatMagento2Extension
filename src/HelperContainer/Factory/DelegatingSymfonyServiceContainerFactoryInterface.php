<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer\Factory;

use SEEC\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;

interface DelegatingSymfonyServiceContainerFactoryInterface
{
    public function create(ConfigInterface $config, array $symfonyServiceContainers): DelegatingSymfonyServiceContainer;
}
