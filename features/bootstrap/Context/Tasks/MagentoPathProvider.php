<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

final class MagentoPathProvider implements MagentoPathProviderInterface
{
    public function getMagentoRootDirectory(): string
    {
        $path = __DIR__;
        $maxCount = count(explode('/', $path));
        $i = 0;
        while (!file_exists(sprintf('%s/app/etc/env.php', $path)) && $i++ < $maxCount) {
            $path = sprintf('%s/..', $path);
        }

        return realpath($path) ?: $path;
    }
}
