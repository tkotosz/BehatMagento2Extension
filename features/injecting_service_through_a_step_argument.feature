Feature: Injecting service from Magento DI to Behat Context through a Behat Step argument
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from the Magento DI into Behat Contexts through a Behat Step argument

  Scenario: Manually Injecting service from Magento DI
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a service has been successfully injected as argument to this step
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Magento\Catalog\Api\ProductRepositoryInterface;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          /**
           * @Given a service has been successfully injected as argument to this step
           */
          public function aServiceHasBeenSuccessfullyInjectedAsArgumentToThisStep(ProductRepositoryInterface $productRepository)
          {
              Assert::assertInstanceOf(ProductRepositoryInterface::class, $productRepository);
          }
      }
      """
    And I have the configuration:
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
