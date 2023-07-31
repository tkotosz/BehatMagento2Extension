@virtual @mocking
Feature: Mocking
  As a developer
  In order to write Behat tests easily
  I should be able to mock some dependencies of the used Magento services

  Background:
    Given I have a Magento module called "Acme_Awesome"
    And I have an interface "Acme\Awesome\Config\ConfigProviderInterface" defined in this module:
      """
      <?php

      namespace Acme\Awesome\Config;

      interface ConfigProviderInterface
      {
          public function isFreeDeliverEnabled(): bool;

          public function getFreeDeliveryThreshold(): float;
      }
      """
    And I have a class "Acme\Awesome\Config\ConfigProvider" defined in this module:
      """
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
      """
    And I have a class "Acme\Awesome\Service\DeliveryCostCalculator" defined in this module:
      """
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
      """
    And I have a class "Acme\Awesome\Test\FakeConfigProvider" defined in this module:
      """
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
      """
    And I have the feature:
      """
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
      """
    And I have the context:
      """
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
      """
    And I run the Magento command "module:enable" with arguments "Acme_Awesome"

  Scenario: Override global service dependency using preference
    Given I have a global Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Config\ConfigProvider" />
      </config>
      """
    And I have a test Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Test\FakeConfigProvider" />
      </config>
      """
    And I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@seec.magento2_extension.service_container'
            magento:
              area: test

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Replace global service dependency with composition
    Given I have a global Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Config\ConfigProvider" />
      </config>
      """
    And I have a test Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <type name="Acme\Awesome\Service\DeliveryCostCalculator">
              <arguments>
                  <argument name="deliveryConfig" xsi:type="object">Acme\Awesome\Test\FakeConfigProvider</argument>
              </arguments>
          </type>
      </config>
      """
    And I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@seec.magento2_extension.service_container'
            magento:
              area: test

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Override frontend service dependency using preference
    Given I have a frontend Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Config\ConfigProvider" />
      </config>
      """
    And I have a test Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Test\FakeConfigProvider" />
      </config>
      """
    And I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@seec.magento2_extension.service_container'
            magento:
              area: [frontend, test]

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Replace frontend service dependency with composition
    Given I have a frontend Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <preference for="Acme\Awesome\Config\ConfigProviderInterface" type="Acme\Awesome\Config\ConfigProvider" />
      </config>
      """
    And I have a test Magento DI configuration in this module:
      """
      <?xml version="1.0"?>
      <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
          <type name="Acme\Awesome\Service\DeliveryCostCalculator">
              <arguments>
                  <argument name="deliveryConfig" xsi:type="object">Acme\Awesome\Test\FakeConfigProvider</argument>
              </arguments>
          </type>
      </config>
      """
    And I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@seec.magento2_extension.service_container'
            magento:
              area: [frontend, test]

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing
