<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer\Loader;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class DelegatingLoaderHelper implements DelegatingLoaderHelperInterface
{
    public function loadFiles(ContainerBuilder $container, string $file, string $basePath): void
    {
        $fileLocator = new FileLocator([$basePath]);
        $delegatingLoader = new DelegatingLoader(
            new LoaderResolver([
                new XmlFileLoader($container, $fileLocator),
                new YamlFileLoader($container, $fileLocator),
                new PhpFileLoader($container, $fileLocator),
            ]),
        );
        $delegatingLoader->load($file);
    }
}
