<?php

namespace Bex\Behat\Magento2Extension\Service;

use Magento\Framework\App\ObjectManager;

class MagentoObjectManager
{
    public function get(string $id)
    {
        return ObjectManager::getInstance()->get($id);
    }

    public function create(string $id)
    {
        return ObjectManager::getInstance()->create($id);
    }
}
