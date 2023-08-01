<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\HelperContainer\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\HelperContainer\Factory\DelegatingSymfonyServiceContainerFactory;
use SEEC\Behat\Magento2Extension\HelperContainer\Factory\DelegatingSymfonyServiceContainerFactoryInterface;
use SEEC\Behat\Magento2Extension\HelperContainer\Loader\DelegatingLoaderHelperInterface;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;

final class DelegatingSymfonyServiceContainerFactoryTest extends TestCase
{
    private DelegatingSymfonyServiceContainerFactoryInterface $factory;

    private string $basePath;

    private DelegatingLoaderHelperInterface|MockObject $helper;

    public function setUp(): void
    {
        $this->basePath = '/path/to/base';
        $this->helper = $this->createMock(DelegatingLoaderHelperInterface::class);
        $this->factory = new DelegatingSymfonyServiceContainerFactory($this->basePath, $this->helper);
    }

    public function test_it_will_not_configure_created_class_when_config_contains_nothing_to_use(): void
    {
        /** @var ConfigInterface|MockObject $config */
        $config = $this->createMock(ConfigInterface::class);
        $symfonyServiceContainers = [];
        $container = $this->factory->create($config, $symfonyServiceContainers);
        $this->helper->expects($this->never())->method('loadFiles');
        $this->assertInstanceOf(DelegatingSymfonyServiceContainer::class, $container);
    }

    public function test_it_will_correctly_create_new_class(): void
    {
        /** @var ConfigInterface|MockObject $config */
        $config = $this->createMock(ConfigInterface::class);
        $symfonyServiceContainers = [];
        $config->expects($this->once())
            ->method('getServicesPath')
            ->willReturn('/var/log/test.yml');

        $this->helper->expects($this->once())
            ->method('loadFiles')
            ->with($this->isInstanceOf(DelegatingSymfonyServiceContainer::class), '/var/log/test.yml', $this->basePath);

        $container = $this->factory->create($config, $symfonyServiceContainers);
        $this->assertInstanceOf(DelegatingSymfonyServiceContainer::class, $container);
    }
}
