<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\HelperContainer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\HelperContainer\Magento2SymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\HelperContainer\ServiceContainerInterface;
use SEEC\Behat\Magento2Extension\Service\MagentoObjectManagerInterface;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;

final class Magento2SymfonyServiceContainerTest extends TestCase
{
    /** @var MockObject|MagentoObjectManagerInterface|object */
    private object $magentoObjectManager;

    private ServiceContainerInterface $container;

    public function setUp(): void
    {
        $this->magentoObjectManager = $this->createMock(MagentoObjectManagerInterface::class);
        $this->container = new Magento2SymfonyServiceContainer($this->magentoObjectManager);
    }

    public function test_it_is_a_container(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->container);
    }

    public function serviceProvider(): array
    {
        return [
            'it has the service available' => [true],
            'it has the service not available' => [false],
        ];
    }

    /** @dataProvider serviceProvider */
    public function test_it_can_evaluate_if_it_has_an_service(bool $expectation): void
    {
        if ($expectation) {
            $this->magentoObjectManager->expects($this->once())
                ->method('get')
                ->with('some_service')
                ->willReturn(new stdClass());
        } else {
            $this->magentoObjectManager->expects($this->once())
                ->method('get')
                ->with('some_service')
                ->willThrowException(new \Exception());
        }

        $this->assertSame($expectation, $this->container->has('some_service'));
    }

    public function test_it_can_correctly_get_the_definition(): void
    {
        $this->assertInstanceOf(Definition::class, $this->container->getDefinition('some_service'));
    }
}
