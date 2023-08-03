<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer\Factory;

use SEEC\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\HelperContainer\Loader\DelegatingLoaderHelperInterface;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;

final class DelegatingSymfonyServiceContainerFactory implements DelegatingSymfonyServiceContainerFactoryInterface
{
    public function __construct(
        private readonly string $basePath,
        private readonly DelegatingLoaderHelperInterface $loaderHelper,
    ) {
    }

    public function create(ConfigInterface $config, array $symfonyServiceContainers): DelegatingSymfonyServiceContainer
    {
        $container = new DelegatingSymfonyServiceContainer($symfonyServiceContainers);
        if (($file = $config->getServicesPath()) !== null) {
            $this->loaderHelper->loadFiles($container, $file, $this->basePath);
        }

        $container->compile();

        return $container;
    }
}
