<?php

namespace Bex\Behat\Magento2Extension\HelperContainer;

use Bex\Behat\Magento2Extension\Service\MagentoObjectManager;
use Interop\Container\ContainerInterface;
use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Registry;
use Magento\User\Model\UserFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyServiceContainer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class Magento2SymfonyServiceContainer extends SymfonyServiceContainer implements ContainerInterface
{
    /**
     * @var MagentoObjectManager
     */
    private $magentoObjectManager;

    public function __construct(MagentoObjectManager $magentoObjectManager)
    {
        parent::__construct();
        $this->magentoObjectManager = $magentoObjectManager;
    }

    public function has($id)
    {
        try {
            $this->magentoObjectManager->get($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        try {
            return $this->magentoObjectManager->get($id);
        } catch (\Exception $e) {
            throw new ServiceNotFoundException($id, null, $e);
        }
    }

    public function getDefinition($id)
    {
        return (new Definition($id, [$id]))->setFactory([new Reference('magento2.object_manager'), 'get']);
    }
}
