@virtual
Feature: Injecting service from Magento DI to Behat Context through the constructor
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from the Magento DI into Behat Contexts through the constructor

  Background:
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a service has been successfully injected through the Context constructor
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Magento\Catalog\Api\ProductRepositoryInterface;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          /** @var ProductRepositoryInterface */
          private $orderRepository;

          public function __construct(ProductRepositoryInterface $productRepository)
          {
              $this->productRepository = $productRepository;
          }

          /**
           * @Given a service has been successfully injected through the Context constructor
           */
          public function aServiceHasBeenSuccessfullyInjectedThroughTheContextConstructor()
          {
              Assert::assertInstanceOf(ProductRepositoryInterface::class, $this->productRepository);
          }
      }
      """

  Scenario: Manually Injecting service from Magento DI
    Given I have the configuration:
      """
      default:
        suites:
          application:
            contexts:
              - FeatureContext:
                - '@Magento\Catalog\Api\ProductRepositoryInterface'
            services: '@seec.magento2_extension.service_container'

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Automatically Injecting service from Magento DI
    Given I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@seec.magento2_extension.service_container'

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should not see a failing test
