<?php

namespace Bex\Behat\Magento2Extension\HelperContainer\Factory;

use Bex\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use Bex\Behat\Magento2Extension\ServiceContainer\Config;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DelegatingSymfonyServiceContainerFactory
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /** @throws Exception */
    public function create(Config $config, array $symfonyServiceContainers): DelegatingSymfonyServiceContainer
    {
        $container = new DelegatingSymfonyServiceContainer($symfonyServiceContainers);

        if (($file = $config->getServicesPath()) !== null) {
            $fileLocator = new FileLocator([$this->basePath]);
            $loader = new DelegatingLoader(
                new LoaderResolver([
                    new XmlFileLoader($container, $fileLocator),
                    new YamlFileLoader($container, $fileLocator),
                    new PhpFileLoader($container, $fileLocator),
                ])
            );
            $loader->load($file);
        }

        $container->compile();

        return $container;
    }
}
