<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\Context\Tasks;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\Purger;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\PurgerInterface;
use SEEC\PhpUnit\Helper\ConsecutiveParams;

final class PurgerTest extends TestCase
{
    use ConsecutiveParams;

    private PurgerInterface $purger;

    public function setUp(): void
    {
        $this->purger = new Purger();
    }

    public function test_it_will_attempt_to_purge_all_tables(): void
    {
        $mockConnection = $this->createMock(AdapterInterface::class);
        $mockConnection->expects($this->once())
            ->method('getTables')
            ->willReturn(['table1', 'table2', 'table3']);
        $mockConnection->expects($this->exactly(3))
            ->method('query')
            ->with(...$this->withConsecutive(
                ['SET FOREIGN_KEY_CHECKS = 0'],
                ['TRUNCATE TABLE table1'],
                ['SET FOREIGN_KEY_CHECKS = 1']
            ));

        $mockSelect = $this->createMock(Select::class);
        $mockConnection->expects($this->exactly(2))
            ->method('select')
            ->willReturn($mockSelect);

        $mockSelect->expects($this->exactly(2))
            ->method('from')
            ->with(...$this->withConsecutive(
                ['table1', 'COUNT(*)'],
                ['table2', 'COUNT(*)']
            ));
        $mockConnection->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls('999', '0');

        $this->purger->purge($mockConnection, ['table3']);
    }
}
