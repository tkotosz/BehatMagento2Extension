<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\HelperContainer;

use Behat\Mink\Session;
use FriendsOfBehat\PageObjectExtension\Element\Element;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyServiceContainer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class TestClass extends Element
{
    public function __construct(Session $session, $minkParameters = [])
    {
        parent::__construct($session, $minkParameters);
    }
}

final class DelegatingSymfonyServiceContainerTest extends TestCase
{
    private DelegatingSymfonyServiceContainer $container;

    public function setUp(): void
    {
        $this->container = new DelegatingSymfonyServiceContainer([]);
    }

    public function test_has_returns_true_if_service_exists(): void
    {
        $mockSession = $this->createMock(Session::class);
        $this->container->set('test_class', new TestClass($mockSession, []));
        $this->assertTrue($this->container->has('test_class'));
        $this->assertInstanceOf(TestClass::class, $this->container->get('test_class'));
    }

    public function test_has_returns_false_if_service_does_not_exist(): void
    {
        $this->assertFalse($this->container->has('my_service'));
    }

    public function test_get_returns_service_if_it_exists(): void
    {
        $mockSession = $this->createMock(Session::class);
        $service = new TestClass($mockSession, []);
        $this->container->set('test_class', $service);
        $this->assertSame($service, $this->container->get('test_class'));
    }

    public function test_it_throws_an_error_if_service_does_not_exist(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->assertNull($this->container->get('my_service'));
    }

    public function test_getDefinition_returns_definition_if_service_exists(): void
    {
        $definition = $this->createMock(Definition::class);
        $this->container->setDefinition(TestClass::class, $definition);

        $this->assertSame($definition, $this->container->getDefinition(TestClass::class));
    }

    public function test_getDefinition_throws_exception_if_service_does_not_exist(): void
    {
        $this->expectException(ServiceNotFoundException::class);

        $this->container->getDefinition('my_service');
    }

    public function test_compile_merges_parameter_bags_of_fallback_containers(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackContainer1->setParameter('param1', 'value1');

        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackContainer2->setParameter('param2', 'value2');

        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);
        $container->compile();

        $this->assertSame(['param1' => 'value1', 'param2' => 'value2'], $container->getParameterBag()->all());
    }

    public function test_get_returns_service_from_fallback_containers(): void
    {
        $mockSession = $this->createMock(Session::class);
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackService1 = new TestClass($mockSession);
        $fallbackContainer1->set('some_other_class', $fallbackService1);

        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackService2 = new TestClass($mockSession);
        $fallbackContainer2->set('test_class', $fallbackService2);

        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);

        $this->assertSame($fallbackService2, $container->get('test_class'));
    }

    public function test_get_returns_null_if_service_does_not_exist_in_fallback_containers(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackContainer2 = new SymfonyServiceContainer();

        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];

        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "test_class".');
        $this->assertNull($container->get('test_class'));
    }

    public function test_getDefinition_returns_definition_from_fallback_containers(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackDefinition1 = new Definition(\stdClass::class);
        $fallbackContainer1->setDefinition('my_service', $fallbackDefinition1);

        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackDefinition2 = new Definition(\stdClass::class);
        $fallbackContainer2->setDefinition('my_service', $fallbackDefinition2);

        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);

        $this->assertSame($fallbackDefinition1, $container->getDefinition('my_service'));
    }

    public function test_getDefinition_throws_exception_if_definition_does_not_exist_in_fallback_containers(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);

        $this->expectException(ServiceNotFoundException::class);
        $container->getDefinition('my_service');
    }

    public function test_has_returns_false_if_service_does_not_exist_in_any_container(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);

        $this->assertFalse($container->has(TestClass::class));
    }

    public function test_has_returns_true_if_service_exists_in_fallback_containers(): void
    {
        $fallbackContainer1 = new SymfonyServiceContainer();
        $fallbackContainer2 = new SymfonyServiceContainer();
        $fallbackContainers = [$fallbackContainer1, $fallbackContainer2];
        $container = new DelegatingSymfonyServiceContainer($fallbackContainers);
        $fallbackContainer1->set('test_class', new \stdClass());

        $this->assertTrue($container->has('test_class'));
    }

    public function test_it_returns_null_when_class_should_be_handled_by_autowiring(): void
    {
        $mockSession = $this->createMock(Session::class);
        $service = new TestClass($mockSession, []);
        $this->container->set('test_class', $service);
        $this->assertNull($this->container->get(TestClass::class));
    }
}
