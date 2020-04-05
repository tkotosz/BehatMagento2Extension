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
              if (!$this->productRepository instanceof ProductRepositoryInterface) {
                  throw new Exception('Something went wrong :(');
              }
          }
      }
      """

  Scenario: Manually Injecting service from Magento DI
    Given I have the configuration:
      """
      default:
        suites:
          application:
            autowire: true
            contexts:
              - FeatureContext
            services: '@bex.magento2_extension.service_container'

        extensions:
          Bex\Behat\Magento2Extension: ~
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
            services: '@bex.magento2_extension.service_container'

        extensions:
          Bex\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should not see a failing test
