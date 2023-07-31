<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

use Webmozart\Assert\Assert;

final class MagentoPathProvider implements MagentoPathProviderInterface
{
    public function getMagentoRootDirectory(): string
    {
        $path = __DIR__;
        $maxCount = count(explode('/', $path));
        $i = 0;
        while (!file_exists(sprintf('%s/app/etc/env.php', $path))) {
            Assert::lessThan($i++, $maxCount, 'Could not find Magento root directory');
            $path = sprintf('%s/..', $path);
        }

        return realpath($path) ?: $path;
    }
}
