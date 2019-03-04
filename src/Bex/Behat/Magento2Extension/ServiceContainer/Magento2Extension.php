<?php

namespace Bex\Behat\Magento2Extension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Magento2Extension implements Extension
{
    private const CONFIG_KEY = 'bex_magento2';
    private const SERVICE_ID_EXTENSION_CONFIG = 'bex.magento2_extension.config';

    public function getConfigKey()
    {
        return self::CONFIG_KEY;
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        // nothing to do here
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode(Config::CONFIG_KEY_MAGENTO_BOOTSTRAP)
                    ->defaultValue(getcwd() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php')
                ->end()
                ->scalarNode(Config::CONFIG_KEY_SERVICES)
                    ->defaultValue(null)
                ->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.xml');
        $extensionConfig = new Config($config);
        $container->set(self::SERVICE_ID_EXTENSION_CONFIG, $extensionConfig);
        $container->set('bex.behat_service_container', $container);
    }

    public function process(ContainerBuilder $container)
    {
        // nothing to do here
    }
}
