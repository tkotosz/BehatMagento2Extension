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
use Magento\Framework\Registry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Backend\Model\Auth\Session;
use Magento\User\Model\UserFactory;

class MagentoObjectManagerInitializer implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SuiteTested::BEFORE => 'initApplication'
        ];
    }

    public function initApplication(BeforeSuiteTested $event)
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

        $bootstrap = Bootstrap::create(BP, $params);
        $magentoObjectManager = ObjectManager::getInstance();

        $configLoader = $magentoObjectManager->get(ConfigLoaderInterface::class);

        $mainArea = array_shift($areas);
        $config = $configLoader->load($mainArea);
        foreach ($areas as $area) {
            $config = array_replace_recursive(
                $config,
                $this->arrayRecursiveDiff($configLoader->load($area), $configLoader->load(Area::AREA_GLOBAL))
            );
        }

        $bootstrap = Bootstrap::create(BP, $params);
        $magentoObjectManager = ObjectManager::getInstance();

        $magentoObjectManager->configure($config);

        $appState = $magentoObjectManager->get(State::class);
        $appState->setAreaCode($mainArea);
    }

    // TODO replace this with one of the nice array diff packages :D
    // copied from http://php.net/manual/en/function.array-diff.php#91756
    private function arrayRecursiveDiff($original, $excluded): array
    {
        $aReturn = [];

        foreach ($original as $key => $value) {
            if (array_key_exists($key, $excluded)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayRecursiveDiff($value, $excluded[$key]);
                    if (count($recursiveDiff)) { $aReturn[$key] = $recursiveDiff; }
                } else {
                    if ($value != $excluded[$key]) {
                        $aReturn[$key] = $value;
                    }
                }
            } else {
                $aReturn[$key] = $value;
            }
        }

        return $aReturn;
    }
}
