<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\Listener;

use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Suite\Suite;
use InvalidArgumentException;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Authorization\Model\Role;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\Listener\MagentoObjectManagerInitListener;
use SEEC\Behat\Magento2Extension\Listener\MagentoObjectManagerInitListenerInterface;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;
use SEEC\PhpUnit\Helper\ConsecutiveParams;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MagentoObjectManagerInitListenerTest extends TestCase
{
    use ConsecutiveParams;

    private MagentoObjectManagerInitListenerInterface $listener;

    /** @var ConfigInterface|MockObject */
    private object $config;

    public function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->listener = new MagentoObjectManagerInitListener($this->config);
    }

    public function test_it_is_an_event_subscriber(): void
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    public function test_it_is_subscribed_to_the_correct_events(): void
    {
        $this->assertSame(['tester.suite_tested.before' => 'initApplication'], $this->listener::getSubscribedEvents());
    }

    public function test_it_will_throw_an_error_when_bootstrap_file_cannot_be_found(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Magento's bootstrap file was not found at path 'some_path'");
        $this->config->expects($this->once())
            ->method('getMagentoBootstrapPath')
            ->willReturn('some_path');

        $mockEvent = $this->createMock(SuiteTested::class);
        $mockSuite = $this->createMock(Suite::class);
        $mockSuite->expects($this->once())
            ->method('getSettings')
            ->willReturn(['magento' => ['area' => 'test']]);
        $mockEvent->expects($this->once())
            ->method('getSuite')
            ->willReturn($mockSuite);

        $this->listener->initApplication($mockEvent);
    }

    public function test_it_will_create_an_admin_user_on_demand_when_bootstrapping_admin_area(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $appState = $this->createMock(State::class);
        $appState->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('adminhtml');
        $registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->addMethods(['setRoleId'])
            ->getMock();

        $sessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['setUser'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager->expects($this->exactly(4))
            ->method('get')
            ->with(...$this->withConsecutive(
                [Registry::class],
                [Collection::class],
                ['Magento\User\Model\User'],
                [Session::class]
            ))
            ->willReturnOnConsecutiveCalls(
                $registryMock,
                $collectionMock,
                $user,
                $sessionMock
            );

        $registryMock->expects($this->once())
            ->method('register')
            ->with('isSecureArea', true);
        $collectionMock->expects($this->once())
            ->method('setRolesFilter');

        $roleMock = $this->createMock(Role::class);
        $collectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($roleMock);
        $roleMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $sessionMock->expects($this->once())
            ->method('setUser')
            ->with($user);

        $this->listener->handleAdminAreaBootstrapping($appState, $objectManager);
    }

    public function testArrayRecursiveDiff(): void
    {
        $original = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => [
                'key4' => 'value4',
                'key5' => 'value5',
            ],
            'key6' => [
                'key7' => 'value7',
                'key8' => 'value8',
            ],
        ];

        $excluded = [
            'key1' => 'value1',
            'key3' => [
                'key5' => 'value5',
            ],
            'key6' => [
                'key8' => 'value8',
            ],
        ];

        $result = $this->listener->arrayRecursiveDiff($original, $excluded);
        $this->assertEquals([
            'key2' => 'value2',
            'key3' => [
                'key4' => 'value4',
            ],
            'key6' => [
                'key7' => 'value7',
            ],
        ], $result);
    }
}
