<?php

namespace Bex\Behat\Magento2Extension\HelperContainer;

use Interop\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyServiceContainer;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class DelegatingSymfonyServiceContainer extends SymfonyServiceContainer implements ContainerInterface
{
    /**
     * @var SymfonyServiceContainer[]
     */
    private $fallbackContainers;

    public function __construct(array $symfonyServiceContainers)
    {
        parent::__construct();
        $this->fallbackContainers = $symfonyServiceContainers;
    }

    public function has($id)
    {
        if (!$this->isSupportedServiceId($id)) {
            return false;
        }

        if (parent::has($id)) {
            return true;
        }

        foreach ($this->fallbackContainers as $serviceContainer) {
            if ($serviceContainer->has($id)) {
                return true;
            }
        }

        return false;
    }

    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (!$this->isSupportedServiceId($id)) {
            return null;
        }

        try {
            return parent::get($id);
        } catch (ServiceNotFoundException $e) {
            // no-op continue
        }

        foreach ($this->fallbackContainers as $serviceContainer) {
            try {
                return $serviceContainer->get($id);
            } catch (\Exception $e) {
                // no-op continue
            }
        }

        throw new ServiceNotFoundException($id);
    }

    public function getDefinition($id)
    {
        try {
            return parent::getDefinition($id);
        } catch (ServiceNotFoundException $e) {
            // no-op continue
        }

        foreach ($this->fallbackContainers as $serviceContainer) {
            try {
                return $serviceContainer->getDefinition($id);
            } catch (ServiceNotFoundException $e) {
                // no-op continue
            }
        }

        throw new ServiceNotFoundException($id);
    }

    public function compile(bool $resolveEnvPlaceholders = false)
    {
        foreach ($this->fallbackContainers as $serviceContainer) {
            $this->parameterBag->add($serviceContainer->getParameterBag()->all());
        }

        parent::compile($resolveEnvPlaceholders);
    }

    private function isSupportedServiceId($id)
    {
        if (is_null($id)) {
            return false;
        }

        // If the Page Object Extension is used then let it handle the autowiring
        // @see \SensioLabs\Behat\PageObjectExtension\Context\Argument\PageObjectArgumentResolver::resolveArguments
        if ($this->isPageObject($id)) {
            return false;
        }

        return true;
    }

    private function isPageObject($id)
    {
        return (
            is_subclass_of($id, '\SensioLabs\Behat\PageObjectExtension\PageObject\Page') ||
            is_subclass_of($id, '\SensioLabs\Behat\PageObjectExtension\PageObject\Element')
        );
    }
}
