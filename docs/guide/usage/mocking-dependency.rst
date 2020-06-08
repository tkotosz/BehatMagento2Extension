Mocking Dependency
==================

Given you have an application service interface like this:

.. code-block:: php

    <?php

    namespace Acme\Awesome\Config;

    interface ConfigProviderInterface
    {
        public function isFreeDeliverEnabled(): bool;

        public function getFreeDeliveryThreshold(): float;
    }

And you have an implementation for this service:

.. code-block:: php

    <?php

    namespace Acme\Awesome\Config;

    use Magento\Framework\App\Config\ScopeConfigInterface;

    class ConfigProvider implements ConfigProviderInterface
    {
        /** @var ScopeConfigInterface */
        private $scopeConfig;

        public function __construct(ScopeConfigInterface $scopeConfig)
        {
            $this->scopeConfig = $scopeConfig;
        }

        public function isFreeDeliverEnabled(): bool
        {
            return $this->scopeConfig->isSetFlag('path/to/config');
        }

        public function getFreeDeliveryThreshold(): float
        {
            return (float) $this->scopeConfig->getValue('path/to/another/config');
        }
    }

And you have the following DI config to mark this implementation as the default implementation:

.. code-block:: xml

    <?xml version="1.0"?>
    <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
        <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Config\ConfigProvider" />
    </config>

In addition to these you have an application service which depends on this config interface, e.g.:

.. code-block:: php

    <?php

    namespace Acme\Awesome\Service;

    use Acme\Awesome\Config\ConfigProviderInterface;

    class DeliveryCostCalculator
    {
        private const DELIVERY_COST = 5.0;

        /** @var ConfigProviderInterface */
        private $deliveryConfig;

        public function __construct(ConfigProviderInterface $deliveryConfig)
        {
            $this->deliveryConfig = $deliveryConfig;
        }

        public function calculate(float $total): float
        {
            if ($this->isFreeDelivery($total)) {
                return 0.0;
            }

            return self::DELIVERY_COST;
        }

        private function isFreeDelivery(float $total): bool
        {
            if (!$this->deliveryConfig->isFreeDeliverEnabled()) {
                return false;
            }

            return $total >= $this->deliveryConfig->getFreeDeliveryThreshold();
        }
    }

When you write your application tests, if you would like to avoid relying on the database, then you either need to mock ``Magento\Framework\App\Config\ScopeConfigInterface`` or ``Acme\Awesome\Config\ConfigProviderInterface``. Lets assume we would like to mock our own ``ConfigProviderInterface`` this time.

First of all we need to configure a ``test`` area in Magento.
We can do this by adding the following to the module's global ``etc/di.xml``:

.. code-block:: xml

    <?xml version="1.0" encoding="utf-8"?>
    <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
        <type name="Magento\Framework\App\AreaList">
            <arguments>
                <argument name="areas" xsi:type="array">
                    <item name="test" xsi:type="null" />
                </argument>
            </arguments>
        </type>
    </config>

Or we can simply install the `Test area Magento 2 module <https://packagist.org/packages/tkotosz/test-area-magento2>`_ which will define an area called ``test`` in the same way. :)

Now we can define our DI overrides in the module's ``etc/test/di.xml``.

It will look like this:

.. code-block:: xml

    <?xml version="1.0"?>
    <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
        <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Test\FakeConfigProvider" />
    </config>

And we are done. After a cache clear everything should be ready to use. If you inject the ``Acme\Awesome\Service\DeliveryCostCalculator`` into your Behat Context then it will use the ``Acme\Awesome\Test\FakeConfigProvider`` which we can freely modify in our tests.

E.g.:

**FakeConfigProvider:**

.. code-block:: php

    <?php

    namespace Acme\Awesome\Test;

    use Acme\Awesome\Config\ConfigProviderInterface;

    class FakeConfigProvider implements ConfigProviderInterface
    {
        /** @var bool */
        private $isFreeDeliveryEnabled = false;

        /** @var float */
        private $freeDeliveryThreshold = 0.0;

        public function isFreeDeliverEnabled(): bool
        {
            return $this->isFreeDeliveryEnabled;
        }

        public function getFreeDeliveryThreshold(): float
        {
            return $this->freeDeliveryThreshold;
        }

        public function enableFreeDelivery(): void
        {
            $this->isFreeDeliveryEnabled = true;
        }

        public function disableFreeDelivery(): void
        {
            $this->isFreeDeliveryEnabled = false;
        }

        public function setFreeDeliveryThreshold(float $threshold): void
        {
            $this->freeDeliveryThreshold = $threshold;
        }
    }
    
    In order to load this custom DI configuration during the test run the test area need to be configured in the Behat test suite so it can load to merge it with the default area.

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          autowire: true
          
          contexts:
            - YourContext
          
          services: '@bex.magento2_extension.service_container'
          
          magento:
            area: test


**Feature:**

.. code-block:: gherkin

  Feature: Delivery Cost Calculation

    Scenario: Standard Delivery applies when under the configured threshold
      Given The the cart total is "98.99"
      And The free delivery is enabled
      And The free delivery cost threshold is configured to "100"
      When The delivery total is calculated
      Then The delivery cost is "5.0"

    Scenario: Free Delivery applies when above the configured threshold
      Given The the cart total is "120"
      And The free delivery is enabled
      And The free delivery cost threshold is configured to "100"
      When The delivery total is calculated
      Then The delivery cost is "0.0"

**Feature Context:**

.. code-block:: php

    <?php

    use Behat\Behat\Context\Context;
    use Acme\Awesome\Service\DeliveryCostCalculator;
    use Acme\Awesome\Test\FakeConfigProvider;
    use PHPUnit\Framework\Assert;

    class FeatureContext implements Context
    {
        /** @var DeliveryCostCalculator */
        private $deliveryCostCalculator;

        /** @type float|null */
        private $cartTotal = null;

        /** @type float|null */
        private $deliveryCost = null;

        public function __construct(DeliveryCostCalculator $deliveryCostCalculator)
        {
            $this->deliveryCostCalculator = $deliveryCostCalculator;
        }

        /**
         * @Given The the cart total is :total
         */
        public function theCartContainsTheFollowingItems(float $total)
        {
            $this->cartTotal = $total;
        }

        /**
         * @Given The free delivery is enabled
         */
        public function theFreeDeliveryIsEnabled(FakeConfigProvider $deliveryConfig)
        {
            $deliveryConfig->enableFreeDelivery();
        }

        /**
         * @Given The free delivery is disabled
         */
        public function theFreeDeliveryIsDisabled(FakeConfigProvider $deliveryConfig)
        {
            $deliveryConfig->disableFreeDelivery();
        }

        /**
         * @Given The free delivery cost threshold is configured to :threshold
         */
        public function theFreeDeliveryCostThresholdIsConfiguredTo(float $threshold, FakeConfigProvider $deliveryConfig)
        {
            $deliveryConfig->setFreeDeliveryThreshold($threshold);
        }

        /**
         * @When The delivery total is calculated
         */
        public function theDeliveryTotalIsCalculated()
        {
            $this->deliveryCost = $this->deliveryCostCalculator->calculate($this->cartTotal);
        }

        /**
         * @Then The delivery cost is :expectedDeliveryCost
         */
        public function theDeliveryCostIs(float $expectedDeliveryCost)
        {
            Assert::assertEquals($expectedDeliveryCost, $this->deliveryCost);
        }
    }
