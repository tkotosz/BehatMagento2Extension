<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Store;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorage;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\AbstractMagentoContext;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\DefaultFixtures;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\DefaultFixturesInterface;

final class StoreContext extends AbstractMagentoContext
{
    private SharedStorage $sharedStorage;

    private AdapterInterface $resourceConnection;

    private DefaultFixturesInterface $fixtureFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultFixtures $fixtureFactory,
        SharedStorage $sharedStorage
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->fixtureFactory = $fixtureFactory;
        $this->resourceConnection = $resourceConnection->getConnection();
    }

    /**
     * @Given a frontend store-view exists
     * @Given a frontend store-view exists with code :code
     * @Given a frontend store-view exists with code :code and name :name
     */
    public function aFrontendStoreViewExistsWithCodeAndName(string $code = 'test_code', string $name = 'Test Name'): void
    {
        $this->fixtureFactory->setSharedStorage($this->sharedStorage);
        $this->fixtureFactory->createDefaults($this->resourceConnection, $code, $name);
    }

    /**
     * @Given a backend store-view exists
     */
    public function aBackendStoreViewExists(): void
    {
        $this->fixtureFactory->setSharedStorage($this->sharedStorage);
        $this->fixtureFactory->createDefaults($this->resourceConnection, 'admin', 'Admin');
    }
}
