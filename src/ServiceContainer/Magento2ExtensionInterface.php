<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Symfony\Component\DependencyInjection\TaggedContainerInterface;

interface Magento2ExtensionInterface extends Extension
{
    public const CONFIG_KEY = 'seec_magento2';

    public const SERVICE_ID_EXTENSION_CONFIG = 'seec.magento2_extension.config';

    public const BEHAT_CONTAINER_KEY = 'seec.behat_service_container';

    public function process(TaggedContainerInterface $container): void;
}
