<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer\Loader;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface DelegatingLoaderHelperInterface
{
    public function loadFiles(ContainerBuilder $container, string $file, string $basePath): void;
}
