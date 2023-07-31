<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\HelperContainer;

use FriendsOfBehat\PageObjectExtension\Element\Element;
use FriendsOfBehat\PageObjectExtension\Page\Page;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyServiceContainer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Throwable;
use Webmozart\Assert\Assert;

final class DelegatingSymfonyServiceContainer extends SymfonyServiceContainer implements ServiceContainerInterface
{
    /**
     * @var SymfonyServiceContainer[]
     */
    private array $fallbackContainers;

    public function __construct(array $symfonyServiceContainers)
    {
        parent::__construct();
        $this->fallbackContainers = $symfonyServiceContainers;
    }

    public function has(string $id): bool
    {
        if ($this->isPageObject($id)) {
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

    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        if ($this->isPageObject($id)) {
            return null;
        }

        try {
            return parent::get($id);
        } catch (ServiceNotFoundException $e) {
        }

        foreach ($this->fallbackContainers as $serviceContainer) {
            try {
                return $serviceContainer->get($id);
            } catch (Throwable $e) {
            }
        }

        throw new ServiceNotFoundException($id);
    }

    public function getDefinition(string $id): Definition
    {
        try {
            return parent::getDefinition($id);
        } catch (ServiceNotFoundException $e) {
        }

        foreach ($this->fallbackContainers as $serviceContainer) {
            try {
                return $serviceContainer->getDefinition($id);
            } catch (ServiceNotFoundException $e) {
            }
        }

        throw new ServiceNotFoundException($id);
    }

    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        foreach ($this->fallbackContainers as $serviceContainer) {
            $this->parameterBag->add($serviceContainer->getParameterBag()->all());
        }

        parent::compile($resolveEnvPlaceholders);
    }

    private function isPageObject(string $id): bool
    {
        Assert::notNull($id);

        return is_subclass_of($id, Page::class) || is_subclass_of($id, Element::class);
    }
}
