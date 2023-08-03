<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

use Magento\Framework\DB\Adapter\AdapterInterface;

final class Purger implements PurgerInterface
{
    public function purge(AdapterInterface $connection, array $excludedTables = []): void
    {
        $connection->query('SET FOREIGN_KEY_CHECKS = 0');
        $tables = $connection->getTables();
        foreach ($tables as $table) {
            if (in_array($table, $excludedTables)) {
                continue;
            }

            $count = $connection->select()->from($table, 'COUNT(*)');
            $result = (int) $connection->fetchOne($count);
            if ($result === 0) {
                continue;
            }

            $sql = sprintf('TRUNCATE TABLE %s', $table);
            $connection->query($sql);
        }
        $connection->query('SET FOREIGN_KEY_CHECKS = 1');
    }
}
