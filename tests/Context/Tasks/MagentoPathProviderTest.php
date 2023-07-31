<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\Context\Tasks;

use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProvider;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProviderInterface;

final class MagentoPathProviderTest extends TestCase
{
    private MagentoPathProviderInterface $magentoPathProvider;

    public function setUp(): void
    {
        $this->magentoPathProvider = new MagentoPathProvider();
    }

    public function test_it_can_get_magento_root_directory(): void
    {
        $this->assertDirectoryExists($this->magentoPathProvider->getMagentoRootDirectory());
    }
}
