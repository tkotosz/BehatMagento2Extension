<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Magento\Framework\App\ResourceConnection;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleaner;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleanerInterface;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\DefaultFixtures;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\DefaultFixturesInterface;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\Purger;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\PurgerInterface;

final class DatabaseHook implements Context, DatabaseHookInterface
{
    private ResourceConnection $resource;

    private DefaultFixturesInterface $fixturesManager;

    private CacheCleanerInterface $cacheCleaner;

    private PurgerInterface $purger;

    public function __construct(
        ResourceConnection $resource,
        DefaultFixtures $defaultFixtures = null,
        CacheCleaner $cacheCleaner = null,
        Purger $purger = null,
    ) {
        $this->resource = $resource;
        $this->fixturesManager = $defaultFixtures ?? new DefaultFixtures();
        $this->cacheCleaner = $cacheCleaner ?? new CacheCleaner();
        $this->purger = $purger ?? new Purger();
    }

    /**
     * @BeforeScenario
     */
    public function purgeAndPrefillWithFixtures(BeforeScenarioScope $scope): void
    {
        $connection = $this->resource->getConnection();

        $this->cacheCleaner->clean();
        $this->purger->purge($connection, [
            'core_config_data',
            'eav_attribute',
            'eav_attribute_group',
            'eav_attribute_label',
            'eav_attribute_option',
            'eav_attribute_option_swatch',
            'eav_attribute_option_value',
            'eav_attribute_set',
            'eav_entity_type',
        ]);
        $this->fixturesManager->createDefaults($connection);
    }
}
