<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\HelperContainer\Helper;

use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\HelperContainer\Loader\DelegatingLoaderHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class DelegatingLoaderHelperTest extends TestCase
{
    public function testLoadFiles(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $file = 'test.yml';
        $basePath = __DIR__;
        $helper = new DelegatingLoaderHelper();
        $helper->loadFiles($container, $file, $basePath);
        $this->assertTrue(true);
    }
}
