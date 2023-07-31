<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ServiceContainerInterface extends ContainerInterface
{
    public function getDefinition(string $id): object;
}
