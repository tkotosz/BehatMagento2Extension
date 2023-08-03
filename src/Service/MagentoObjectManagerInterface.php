<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Service;

interface MagentoObjectManagerInterface
{
    public function get(string $id): object;

    public function create(string $id): object;
}
