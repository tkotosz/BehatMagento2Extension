<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Service;

use Magento\Framework\App\ObjectManager;

final class MagentoObjectManager implements MagentoObjectManagerInterface
{
    public function get(string $id): object
    {
        return ObjectManager::getInstance()->get($id);
    }

    public function create(string $id): object
    {
        return ObjectManager::getInstance()->create($id);
    }
}
