<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

interface CacheCleanerInterface
{
    public function clean(bool $cleanObjectManager = true): void;
}
