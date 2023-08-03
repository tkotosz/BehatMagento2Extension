<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks;

use DomainException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorage;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorageInterface;
use Throwable;
use Webmozart\Assert\Assert;

final class DefaultFixtures implements DefaultFixturesInterface
{
    private ?SharedStorageInterface $sharedStorage = null;

    public function setSharedStorage(SharedStorageInterface $sharedStorage): void
    {
        $this->sharedStorage = $sharedStorage;
    }

    private function getSharedStorage(): ?SharedStorageInterface
    {
        return $this->sharedStorage;
    }

    private function getRepository(string $class): object
    {
        Assert::interfaceExists($class);

        return $this->getObjectManager()->create($class);
    }

    public function createDefaults(AdapterInterface $connection, string $code = 'test_code', string $name = 'Test Name'): void
    {
        $stock = $this->getOrCreateStock($name, $connection);
        $website = $this->getOrCreateWebsite($code, $name);
        $group = $this->getOrCreateGroup($code, $name, $website, $connection);
        $store = $this->getOrCreateStore($code, $name, $website, $group);

        if ($this->getSharedStorage() instanceof SharedStorage) {
            $this->getSharedStorage()->set('stock', $stock);
            $this->getSharedStorage()->set('website', $website);
            $this->getSharedStorage()->set('group', $group);
            $this->getSharedStorage()->set('store', $store);
        }
    }

    private function getObjectManager(): ObjectManager
    {
        return ObjectManager::getInstance();
    }

    private function getOrCreateWebsite(string $code, string $name): WebsiteInterface
    {
        $existing = $this->getExistingEntity(WebsiteInterface::class, $code);
        if ($existing !== null) {
            Assert::isInstanceOf($existing, WebsiteInterface::class);

            return $existing;
        }

        /** @var WebsiteInterface $website */
        $website = $this->getObjectManager()->create(WebsiteInterface::class);
        $website->setCode($code);
        $website->setName($name);
        $website->setDefaultGroupId(1);
        $website->setData('is_default', true); /** @phpstan-ignore-line */
        $this->getObjectManager()->create(Website::class)->save($website);
        Assert::isInstanceOf($website, WebsiteInterface::class);

        return $website;
    }

    private function getOrCreateGroup(string $code, string $name, WebsiteInterface $website, AdapterInterface $connection): GroupInterface
    {
        $existing = $this->getExistingEntity(GroupInterface::class, $code);
        if ($existing !== null) {
            Assert::isInstanceOf($existing, GroupInterface::class);

            return $existing;
        }

        $group = null;

        try {
            /** @var GroupInterface $group */
            $group = $this->getObjectManager()->create(GroupInterface::class);
            $group->setCode($code);
            $group->setName($name);
            $group->setRootCategoryId(1);
            $group->setDefaultStoreId(1);
            $group->setWebsiteId($website->getId());

            $this->getObjectManager()->create(Group::class)->save($group);
        } catch (NoSuchEntityException $e) {
        } catch (Throwable $e) {
            $connection->insert('store_group', [
                'group_id' => 1,
                'website_id' => $website->getId(),
                'code' => $code,
                'name' => $name,
                'root_category_id' => 1,
                'default_store_id' => 1,
            ]);
            /** @var GroupRepositoryInterface $repository */
            $repository = $this->getObjectManager()->create(GroupRepositoryInterface::class);
            $group = $repository->get(1);
        }

        Assert::isInstanceOf($group, GroupInterface::class);

        $website->setDefaultGroupId($group->getId());
        $this->getObjectManager()->create(Website::class)->save($website);

        return $group;
    }

    private function getOrCreateStore(string $code, string $name, WebsiteInterface $website, GroupInterface $group): StoreInterface
    {
        $existing = $this->getExistingEntity(StoreInterface::class, $code);
        if ($existing !== null) {
            Assert::isInstanceOf($existing, StoreInterface::class);

            return $existing;
        }

        /** @var StoreInterface $store */
        $store = $this->getObjectManager()->create(StoreInterface::class);
        $store->setCode($code);
        $store->setName($name);
        $store->setWebsiteId($website->getId());
        $store->setStoreGroupId($group->getId());
        $store->setIsActive(1);

        $this->getObjectManager()->create(Store::class)->save($store);
        Assert::isInstanceOf($store, StoreInterface::class);

        $group->setDefaultStoreId($store->getId());
        $this->getObjectManager()->create(Group::class)->save($group);

        return $store;
    }

    public function getOrCreateStock(string $name, AdapterInterface $connection): StockInterface
    {
        $existing = $this->getExistingEntity(StockInterface::class, $name);
        if ($existing !== null) {
            Assert::isInstanceOf($existing, StockInterface::class);

            return $existing;
        }

        /** @var StockRepositoryInterface $repo */
        $repo = $this->getRepository(StockRepositoryInterface::class);

        try {
            /** @var StockInterface $stock */
            $stock = $this->getObjectManager()->create(StockInterface::class);
            $stock->setName($name);
            $repo->save($stock);
        } catch (DomainException|CouldNotSaveException $e) {
            echo sprintf(
                'Could not create stock regularly, retry with direct injection. Error: %s, File: %s:%s',
                $e->getPrevious()?->getMessage() ?? $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            ) . \PHP_EOL;
            $connection->insert('inventory_stock', ['stock_id' => 1, 'name' => $name]);
            $stock = $repo->get(1);
        }

        Assert::isInstanceOf($stock, StockInterface::class);

        return $stock;
    }

    private function getExistingEntity(string $interface, string $identifier): null|GroupInterface|StoreInterface|WebsiteInterface|StockInterface
    {
        $checkBy = 'getCode';
        switch ($interface) {
            case WebsiteInterface::class:
                /** @var WebsiteRepositoryInterface $repository */
                $repository = $this->getRepository(WebsiteRepositoryInterface::class);
                $repository->clean();

                break;
            case GroupInterface::class:
                /** @var GroupRepositoryInterface $repository */
                $repository = $this->getRepository(GroupRepositoryInterface::class);
                $repository->clean();

                break;
            case StoreInterface::class:
                /** @var StoreRepositoryInterface $repository */
                $repository = $this->getRepository(StoreRepositoryInterface::class);
                $repository->clean();

                break;
            case StockInterface::class:
                /** @var StockRepositoryInterface $repository */
                $repository = $this->getRepository(StockRepositoryInterface::class);
                $checkBy = 'getName';

                break;
            default:
                throw new \InvalidArgumentException('Interface not found');
        }

        $existingList = is_array($repository->getList())
            ? $repository->getList()
            : $repository->getList()->getItems();

        foreach ($existingList as $existing) {
            if ($existing->$checkBy() === $identifier) {
                Assert::isInstanceOf($existing, $interface);

                return $existing;
            }
        }

        return null;
    }
}
