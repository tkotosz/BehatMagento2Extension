<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Stock;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorage;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\AbstractMagentoContext;
use Webmozart\Assert\Assert;

final class StockContext extends AbstractMagentoContext
{
    private StockRepositoryInterface $stockRepository;

    private SharedStorage $sharedStorage;

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        StockRepositoryInterface $stockRepository,
        SearchCriteriaBuilder $searchCriteriaFactory,
        SharedStorage $sharedStorage
    ) {
        $this->stockRepository = $stockRepository;
        $this->sharedStorage = $sharedStorage;
        $this->searchCriteriaBuilder = $searchCriteriaFactory;
    }

    /**
     * @Given the application has a default stock entity
     * @Given the application has a stock entity with name :name
     */
    public function theApplicationHasStockWithName(string $name = 'Default'): void
    {
        $filter = $this->searchCriteriaBuilder->addFilter('name', $name)->create();
        $existingStock = $this->stockRepository->getList($filter);
        if ($existingStock->getTotalCount() > 0) {
            $stock = $existingStock->getItems()[0];
        } else {
            /** @var StockINterface $stock */
            $stock = $this->getObjectManager()->create(StockInterface::class);
            $stock->setName($name);
            $this->stockRepository->save($stock);
        }

        Assert::isInstanceOf($stock, StockInterface::class);
        $this->sharedStorage->set('stock', $existingStock);
    }
}
