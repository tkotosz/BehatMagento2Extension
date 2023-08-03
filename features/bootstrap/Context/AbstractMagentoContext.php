<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context;

use Behat\Behat\Context\Context;
use Magento\Framework\App\ObjectManager;

abstract class AbstractMagentoContext implements Context
{
    protected function getObjectManager(): ObjectManager
    {
        return ObjectManager::getInstance();
    }
}
