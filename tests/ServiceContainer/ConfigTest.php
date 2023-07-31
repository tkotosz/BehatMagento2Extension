<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\ServiceContainer;

use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\ServiceContainer\Config;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;

final class ConfigTest extends TestCase
{
    private ConfigInterface $config;

    public function setUp(): void
    {
        $this->config = new Config([
            ConfigInterface::CONFIG_KEY_SERVICES => 'services',
            ConfigInterface::CONFIG_KEY_MAGENTO_BOOTSTRAP => 'bootstrap',
        ]);
    }

    public function test_it_can_get_service_parameter(): void
    {
        $this->assertSame('services', $this->config->getServicesPath());
    }

    public function test_it_can_get_bootstrap_parameter(): void
    {
        $this->assertSame('bootstrap', $this->config->getMagentoBootstrapPath());
    }
}
