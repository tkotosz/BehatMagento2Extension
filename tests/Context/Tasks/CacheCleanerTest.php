<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Tests\Unit\Context\Tasks;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleaner;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleanerInterface;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class CacheCleanerTest extends TestCase
{
    private CacheCleanerInterface $cacheCleaner;

    private MagentoPathProviderInterface|MockObject $pathProvider;

    private Filesystem|MockObject $fileSystem;

    private Finder|MockObject $finder;

    public function setUp(): void
    {
        $this->pathProvider = $this->createMock(MagentoPathProviderInterface::class);
        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->finder = $this->createMock(Finder::class);
        $this->cacheCleaner = new CacheCleaner($this->pathProvider, $this->fileSystem, $this->finder);
    }

    public function test_it_will_attempt_to_clear_the_cache_correctly(): void
    {
        $this->pathProvider->expects($this->once())
            ->method('getMagentoRootDirectory')
            ->willReturn('/var/www/html');

        $this->finder->expects($this->once())
            ->method('in')
            ->with('/var/www/html/var/cache')
            ->willReturnSelf();

        $this->fileSystem->expects($this->once())
            ->method('remove')
            ->with($this->finder);

        $this->cacheCleaner->clean(false);
    }
}
