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
use Symfony\Component\Finder\SplFileInfo;

final class CacheCleanerTest extends TestCase
{
    private CacheCleanerInterface $cacheCleaner;

    /** @var MagentoPathProviderInterface|MockObject|object */
    private object $pathProvider;

    /** @var Filesystem|MockObject|object */
    private object $fileSystem;

    /** @var Finder|MockObject|object */
    private object $finder;

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

        $file1 = $this->createMock(SplFileInfo::class);
        $file1->expects($this->once())
            ->method('getRelativePathname')
            ->willReturn('.');
        $file1->expects($this->never())
            ->method('getPathname');

        $file2 = $this->createMock(SplFileInfo::class);
        $file2->expects($this->once())
            ->method('getRelativePathname')
            ->willReturn('test');
        $file2->expects($this->once())
            ->method('getPathname')
            ->willReturn('/var/www/html/var/cache/test');

        $this->finder->expects($this->once())
            ->method('in')
            ->with('/var/www/html/var/cache/')
            ->willReturn([$file1, $file2]);

        $this->fileSystem->expects($this->once())
            ->method('remove')
            ->with('/var/www/html/var/cache/test');

        $this->cacheCleaner->clean(false);
    }
}
