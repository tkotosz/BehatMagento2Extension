<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

use Magento\Framework\DB\Adapter\AdapterInterface;

interface PurgerInterface
{
    public function purge(AdapterInterface $connection, array $excludedTables = []): void;
}
