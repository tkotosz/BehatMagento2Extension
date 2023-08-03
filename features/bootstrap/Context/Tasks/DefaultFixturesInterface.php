<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

use Magento\Framework\DB\Adapter\AdapterInterface;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorageAwareInterface;

interface DefaultFixturesInterface extends SharedStorageAwareInterface
{
    public function createDefaults(AdapterInterface $connection, string $code = 'test_code', string $name = 'Test Name'): void;
}
