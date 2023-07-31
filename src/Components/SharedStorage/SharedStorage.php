<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Components\SharedStorage;

use Webmozart\Assert\Assert;

final class SharedStorage implements SharedStorageInterface
{
    private array $data = [];

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key)
    {
        Assert::keyExists($this->data, $key);

        return $this->data[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function setClipboard(array $clipboard): void
    {
        $this->data = array_merge($this->data, $clipboard);
    }
}
