Mocking Dependency
==================

Given you have an application service interface like this:

.. code-block:: php

    <?php

    namespace Vendor\ModuleName\Config;

    interface ConfigProviderInterface
    {
        public function isFreeDeliverEnabled(): bool;

        public function getFreeDeliveryThreshold(): float;
    }

And you have an implementation for this service:

.. code-block:: php

    <?php

    namespace Vendor\ModuleName\Config;

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

    <preference for="Vendor\ModuleName\Config\ConfigProviderInterface" type="Vendor\ModuleName\Config\ConfigProvider" />

In addition to these you have an application service which depends on this config interface, e.g.:

.. code-block:: php

    <?php

    namespace Vendor\ModuleName\Service;

    use Magento\Quote\Model\Quote;

    class DeliveryCostCalculator implements DeliveryCostCalculatorInterface
    {
        private const DELIVERY_COST = 5.0;

        /** @var ConfigProviderInterface */
        private $deliveryConfig;

        public function __construct(ConfigProviderInterface $deliveryConfig)
        {
            $this->deliveryConfig = $deliveryConfig;
        }

        public function calculate(Quote $quote): float
        {
            if (!$this->deliveryConfig->isFreeDeliverEnabled()) {
                return self::DELIVERY_COST;
            }

            if ($quote->getGrandTotal() < $this->deliveryConfig->getFreeDeliveryThreshold()) {
                return self::DELIVERY_COST;
            }

            return 0.0;
        }
    }

When you write your application tests, if you would like to avoid relying on the database, then you either need to mock ``Magento\Framework\App\Config\ScopeConfigInterface`` or ``Vendor\ModuleName\Config\ConfigProviderInterface``. Lets assume we would like to mock our own ``ConfigProviderInterface`` this time.

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
        <preference for="Vendor\ModuleName\Config\ConfigProviderInterface" type="Vendor\ModuleName\Test\FakeConfigProvider" />
    </config>

And we are done. After a cache clear everything should be ready to use. If you inject the ``Vendor\ModuleName\Service\DeliveryCostCalculator`` into your Behat Context then it will use the ``Vendor\ModuleName\Test\FakeConfigProvider`` which we can freely modify in our tests.

E.g.:

**FakeConfigProvider:**

.. code-block:: php

    <?php

    namespace Vendor\ModuleName\Test;

    use Magento\Framework\App\Config\ScopeConfigInterface;

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
            return (float) $this->freeDeliveryThreshold;
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

**DeliveryContext:**

.. code-block:: php

    <?php

    use Behat\Behat\Context\Context;
    use Behat\Gherkin\Node\TableNode;
    use Exception;
    use Vendor\ModuleName\Service\DeliveryCostCalculator;
    use Vendor\ModuleName\Test\FakeConfigProvider;

    class DeliveryContext implements Context
    {
        /** @var DeliveryCostCalculator */
        private $deliveryCostCalculator;

        /** @type float|null */
        private $deliveryCost = null;

        public function __construct(DeliveryCostCalculator $deliveryCostCalculator)
        {
            $this->deliveryCostCalculator = $deliveryCostCalculator;
        }

        /**
         * @Given The cart contains the following items:
         */
        public function theCartContainsTheFollowingItems(TableNode $table)
        {
            // Create a Cart here
            // $this->currentQuote = ...
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
         * @When The delivery cost is calculated
         */
        public function theDeliveryCostIsCalculated()
        {
            $this->deliveryCost = $this->deliveryCostCalculator->calculate($this->currentQuote);
        }

        /**
         * @Then The delivery cost is :expectedDeliveryCost
         */
        public function theDeliveryCostIs(float $expectedDeliveryCost)
        {
            if ($expectedDeliveryCost !== $this->deliveryCost) {
                throw new Exception(
                    spritf('Delivery cost expected to be %s but got %s', $expectedDeliveryCost, $this->deliveryCost)
                );
            }
        }
    }

The above context is not complete, it is just an example to show how easy to mock the dependencies this way.
