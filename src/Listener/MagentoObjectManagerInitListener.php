<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Listener;

use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Authorization\Model\Role;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList as DirectoryListAlias;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use SEEC\Behat\Magento2Extension\ServiceContainer\ConfigInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Webmozart\Assert\Assert;

final class MagentoObjectManagerInitListener implements MagentoObjectManagerInitListenerInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SuiteTested::BEFORE => 'initApplication',
        ];
    }

    public function initApplication(SuiteTested $event): void
    {
        $areas = $this->getAreas($event);

        // fix issues with Target component
        $target = new Target(self::class);

        $bootstrapPath = $this->config->getMagentoBootstrapPath();
        Assert::fileExists($bootstrapPath, sprintf("Magento's bootstrap file was not found at path '%s'", $bootstrapPath));
        include $bootstrapPath;

        $_SERVER[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] = [
            DirectoryList::PUB => [DirectoryListAlias::URL_PATH => ''],
            DirectoryList::MEDIA => [DirectoryListAlias::URL_PATH => 'media'],
            DirectoryList::STATIC_VIEW => [DirectoryListAlias::URL_PATH => 'static'],
            DirectoryList::UPLOAD => [DirectoryListAlias::URL_PATH => 'media/upload'],
        ];

        Bootstrap::create(BP, $_SERVER); /** @phpstan-ignore-line */
        $magentoObjectManager = ObjectManager::getInstance();

        $configLoader = $magentoObjectManager->get(ConfigLoaderInterface::class);

        $mainArea = array_shift($areas);
        $config = $configLoader->load($mainArea);
        foreach ($areas as $area) {
            $config = array_replace_recursive(
                $config,
                $this->arrayRecursiveDiff($configLoader->load($area), $configLoader->load(Area::AREA_GLOBAL)),
            );
        }

        Bootstrap::create(BP, $_SERVER); /** @phpstan-ignore-line */
        $magentoObjectManager = ObjectManager::getInstance();

        $magentoObjectManager->configure($config);

        $appState = $magentoObjectManager->get(State::class);
        $appState->setAreaCode($mainArea);

        $this->handleAdminAreaBootstrapping($appState, $magentoObjectManager);
    }

    public function arrayRecursiveDiff(array $original, array $excluded): array
    {
        $aReturn = [];
        foreach ($original as $key => $value) {
            if (array_key_exists($key, $excluded)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayRecursiveDiff($value, $excluded[$key]);
                    if (count($recursiveDiff) > 0) {
                        $aReturn[$key] = $recursiveDiff;
                    }
                } elseif ($value !== $excluded[$key]) {
                    $aReturn[$key] = $value;
                }
            } else {
                $aReturn[$key] = $value;
            }
        }

        return $aReturn;
    }

    private function getAreas(SuiteTested $event): array
    {
        $areas = $event->getSuite()->getSettings()['magento']['area'] ?? Area::AREA_GLOBAL;

        if (is_string($areas)) {
            $areas = [$areas];
        }

        if (!is_array($areas)) {
            $areas = [$areas];
        }

        return $areas;
    }

    public function handleAdminAreaBootstrapping(State $appState, ObjectManager $magentoObjectManager): void
    {
        if ($appState->getAreaCode() === Area::AREA_ADMINHTML) {
            $registry = $magentoObjectManager->get(Registry::class);
            $registry->register('isSecureArea', true);
            $roleCollection = $magentoObjectManager->get(Collection::class);
            $roleCollection->setRolesFilter();

            /** @var Role $adminRole */
            $adminRole = $roleCollection->getFirstItem();

            /** @var User $user */
            $user = $magentoObjectManager->get(User::class);
            $user->setRoleId($adminRole->getId()); /** @phpstan-ignore-line */

            /** @var Session $session */
            $session = $magentoObjectManager->get(Session::class);
            $session->setUser($user);
        }
    }
}
