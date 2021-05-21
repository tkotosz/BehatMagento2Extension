<?php

namespace Bex\Behat\Magento2Extension\Listener;

use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Bex\Behat\Magento2Extension\ServiceContainer\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Rogervila\ArrayDiffMultidimensional;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MagentoObjectManagerInitializer implements EventSubscriberInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SuiteTested::BEFORE => 'initApplication'
        ];
    }

    public function initApplication(BeforeSuiteTested $event): void
    {
        $areas = $event->getSuite()->getSettings()['magento']['area'] ?? Area::AREA_GLOBAL;

        if (is_string($areas)) {
            $areas = [$areas];
        }

        $mainArea = array_shift($areas);
        $rootDir = $this->loadMagentoBootstrap();
        $config = $this->loadDiConfig($rootDir, $mainArea, $areas);

        $magentoObjectManager = $this->createObjectManager($rootDir);
        $magentoObjectManager->configure($config);

        $appState = $magentoObjectManager->get(State::class);
        $appState->setAreaCode($mainArea);
    }

    private function loadDiConfig(string $rootDir, string $mainArea, array $testAreas): array
    {
        $configLoader = $this->createObjectManager($rootDir)->get(ConfigLoaderInterface::class);

        $config = $configLoader->load($mainArea);
        $globalAreaConfig = $configLoader->load(Area::AREA_GLOBAL);
        foreach ($testAreas as $testArea) {
            $testAreaConfig = $configLoader->load($testArea);
            $configOverrides = ArrayDiffMultidimensional::compare($testAreaConfig, $globalAreaConfig);
            $config = array_replace_recursive($config, $configOverrides);
        }

        return $config;
    }

    private function loadMagentoBootstrap(): string
    {
        $bootstrapPath = $this->config->getMagentoBootstrapPath();

        if (!file_exists($bootstrapPath)) {
            throw new \RuntimeException(sprintf("Magento's bootstrap file was not found at path '%s'", $bootstrapPath));
        }

        (static function() use ($bootstrapPath) {
            require_once $bootstrapPath;
        })();

        return dirname(dirname($bootstrapPath));
    }

    private function createObjectManager(string $rootDir): ObjectManagerInterface
    {
        return Bootstrap::create($rootDir, [])->getObjectManager();
    }
}
