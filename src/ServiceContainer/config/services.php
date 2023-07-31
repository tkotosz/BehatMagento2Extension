<?php

declare(strict_types=1);

use SEEC\Behat\Magento2Extension\Components\SharedStorage\SharedStorage;
use SEEC\Behat\Magento2Extension\HelperContainer\DelegatingSymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\HelperContainer\Factory\DelegatingSymfonyServiceContainerFactory;
use SEEC\Behat\Magento2Extension\HelperContainer\Loader\DelegatingLoaderHelper;
use SEEC\Behat\Magento2Extension\HelperContainer\Magento2SymfonyServiceContainer;
use SEEC\Behat\Magento2Extension\Listener\MagentoObjectManagerInitListener;
use SEEC\Behat\Magento2Extension\Service\MagentoObjectManager;
use SEEC\Behat\Magento2Extension\ServiceContainer\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('seec.magento2_extension.config', Config::class);

    $services->set('magento2.object_manager', MagentoObjectManager::class)
        ->public();

    $services->set(
        'seec.behat.magento2_extension.helper_container.loader.delegating_loader_helper',
        DelegatingLoaderHelper::class
    )->public();

    $services->set(
        'seec.behat.magento2_extension.delegating_symfony_service_container_factory',
        DelegatingSymfonyServiceContainerFactory::class
    )
        ->public()
        ->args([
            '%paths.base%',
            service('seec.behat.magento2_extension.helper_container.loader.delegating_loader_helper'),
        ]);

    $services->set('seec.magento2_extension.magento2_service_container', Magento2SymfonyServiceContainer::class)
        ->public()
        ->args([
            service('magento2.object_manager'),
        ])
        ->share(false);

    $services->set('seec.behat_service_container', ContainerBuilder::class);

    $services->set('seec.magento2_extension.service_container', DelegatingSymfonyServiceContainer::class)
        ->public()
        ->tag('helper_container.container')
        ->args([
            service('seec.magento2_extension.config'),
            [
                service('seec.behat_service_container'),
                service('seec.magento2_extension.magento2_service_container'),
            ],
        ])
        ->factory([
            service('seec.behat.magento2_extension.delegating_symfony_service_container_factory'),
            'create',
        ])
        ->share(false);

    $services->set(
        'seec.magento2_extension.object_manager_initializer_listener',
        MagentoObjectManagerInitListener::class
    )
        ->tag('event_dispatcher.subscriber')
        ->args([service('seec.magento2_extension.config')]);

    $services->set('seec.magento2_extension.shared_storage', SharedStorage::class)
        ->public()
        ->share();
};
