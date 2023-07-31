<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\ServiceContainer;

use Behat\Testwork\ServiceContainer\ExtensionManager;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;
use SEEC\Behat\Magento2Extension\ServiceContainer\Magento2Extension;
use SEEC\Behat\Magento2Extension\ServiceContainer\Magento2ExtensionInterface;
use SEEC\PhpUnit\Helper\ConsecutiveParams;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

final class Magento2ExtensionTest extends TestCase
{
    use ConsecutiveParams;

    private Magento2ExtensionInterface $extension;

    public function setUp(): void
    {
        $this->extension = new Magento2Extension();
    }

    public function test_it_can_get_config_key(): void
    {
        $this->assertSame('seec_magento2', $this->extension->getConfigKey());
    }

    public function test_it_can_configure(): void
    {
        $builder = new ArrayNodeDefinition('root', new NodeBuilder());
        $this->extension->configure($builder);
        $children = $builder->getChildNodeDefinitions();
        $this->assertArrayHasKey(ConfigInterface::CONFIG_KEY_MAGENTO_BOOTSTRAP, $children);
        $this->assertArrayHasKey(ConfigInterface::CONFIG_KEY_SERVICES, $children);
    }

    public function test_it_has_noop_functions(): void
    {
        $manager = new ExtensionManager([]);
        $this->extension->initialize($manager);
        $containerBuilder = $this->createMock(TaggedContainerInterface::class);
        $this->extension->process($containerBuilder);
        $this->assertTrue(true);
    }

    public function test_it_can_correctly_load_config_into_container(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder->expects($this->exactly(2))
            ->method('set')
            ->with(...$this->withConsecutive(
                ['seec.magento2_extension.config', $this->isInstanceOf(ConfigInterface::class)],
                ['seec.behat_service_container', $containerBuilder]
            ));

        $this->extension->load($containerBuilder, [
            'bootstrap' => 'bootstrap',
            'services' => 'services',
        ]);
    }
}
