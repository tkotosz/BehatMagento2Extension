<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Listener;

use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface MagentoObjectManagerInitListenerInterface extends EventSubscriberInterface
{
    public function initApplication(SuiteTested $event): void;

    public function arrayRecursiveDiff(array $original, array $excluded): array;

    public function handleAdminAreaBootstrapping(State $appState, ObjectManager $magentoObjectManager): void;
}
