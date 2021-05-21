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

        $bootstrapPath = $this->config->getMagentoBootstrapPath();

        if (!file_exists($bootstrapPath)) {
            throw new \RuntimeException(sprintf("Magento's bootstrap file was not found at path '%s'", $bootstrapPath));
        }

        include $bootstrapPath;

        $params = $_SERVER;

        // TODO Can we remove this?
        $params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] = [
            DirectoryList::PUB => [DirectoryList::URL_PATH => ''],
            DirectoryList::MEDIA => [DirectoryList::URL_PATH => 'media'],
            DirectoryList::STATIC_VIEW => [DirectoryList::URL_PATH => 'static'],
            DirectoryList::UPLOAD => [DirectoryList::URL_PATH => 'media/upload'],
        ];

        $rootDir = dirname($bootstrapPath);

        Bootstrap::create($rootDir, $params);
        $magentoObjectManager = ObjectManager::getInstance();

        $configLoader = $magentoObjectManager->get(ConfigLoaderInterface::class);

        $mainArea = array_shift($areas);
        $config = $configLoader->load($mainArea);
        foreach ($areas as $area) {
            $config = array_replace_recursive(
                $config,
                ArrayDiffMultidimensional::compare($configLoader->load($area), $configLoader->load(Area::AREA_GLOBAL))
            );
        }

        Bootstrap::create($rootDir, $params);
        $magentoObjectManager = ObjectManager::getInstance();

        $magentoObjectManager->configure($config);

        $appState = $magentoObjectManager->get(State::class);
        $appState->setAreaCode($mainArea);
    }
}
