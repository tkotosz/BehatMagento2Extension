<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer;

use SEEC\Behat\Magento2Extension\Service\MagentoObjectManagerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyServiceContainer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

final class Magento2SymfonyServiceContainer extends SymfonyServiceContainer implements ServiceContainerInterface
{
    public function __construct(
        private readonly MagentoObjectManagerInterface $magentoObjectManager,
    ) {
        parent::__construct();
    }

    public function has($id): bool
    {
        try {
            $this->get($id);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        try {
            return $this->magentoObjectManager->get($id);
        } catch (\Throwable $e) {
            throw new ServiceNotFoundException($id, null, $e);
        }
    }

    public function getDefinition(string $id): Definition
    {
        return (new Definition($id, [$id]))->setFactory([new Reference('magento2.object_manager'), 'get']);
    }
}
