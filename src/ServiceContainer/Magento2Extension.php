<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\ServiceContainer;

use Behat\Testwork\ServiceContainer\ExtensionManager;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProvider;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Webmozart\Assert\Assert;

final class Magento2Extension implements Magento2ExtensionInterface
{
    public function getConfigKey(): string
    {
        return self::CONFIG_KEY;
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder /** @phpstan-ignore-line */
            ->children()
                ->scalarNode(ConfigInterface::CONFIG_KEY_MAGENTO_BOOTSTRAP)
                    ->defaultValue($this->getMagentoBootstrapPath())
                ->end()
                ->scalarNode(ConfigInterface::CONFIG_KEY_SERVICES)
                    ->defaultValue(null)
                ->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $locator = new FileLocator(__DIR__ . '/config');
        $loader = new PhpFileLoader($container, $locator);
        Assert::fileExists($locator->locate('services.php'));
        $loader->load('services.php');
        $extensionConfig = new Config($config);
        $container->addCompilerPass(new RegisterListenersPass());
        $container->set(self::SERVICE_ID_EXTENSION_CONFIG, $extensionConfig);
        $container->set(self::BEHAT_CONTAINER_KEY, $container);
    }

    public function process(TaggedContainerInterface $container): void
    {
    }

    private function getMagentoBootstrapPath(): string
    {
        $magentoPathProvider = new MagentoPathProvider();

        return sprintf('%s/app/bootstrap.php', $magentoPathProvider->getMagentoRootDirectory());
    }
}
