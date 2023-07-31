<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Components\SharedStorage;

interface SharedStorageInterface
{
    /**
     * @param string|bool|object|int|array $value
     */
    public function set(string $key, $value): void;

    /**
     * @return string|bool|object|int|array
     */
    public function get(string $key);

    public function has(string $key): bool;

    public function setClipboard(array $clipboard): void;
}
