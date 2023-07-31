<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Components\SharedStorage;

interface SharedStorageAwareInterface
{
    public function setSharedStorage(SharedStorageInterface $sharedStorage): void;
}
