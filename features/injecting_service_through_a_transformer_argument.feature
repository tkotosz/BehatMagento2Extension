@virtual
Feature: Injecting service from Magento DI to Behat Context through a Transformer argument
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from the Magento DI into Behat Contexts through a Transformer argument

  Scenario: Automatically Injecting service from Magento DI to a Behat Step Argument Transformer
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a service has been successfully injected to the parameter transformation method while transforming "foobar"
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Magento\Catalog\Api\Data\ProductInterface;
      use Magento\Catalog\Api\Data\ProductInterfaceFactory as ProductFactory;
      use Magento\Catalog\Api\ProductRepositoryInterface;
      use PHPUnit\Framework\Assert;

      class FeatureContext implements Context
      {
          /**
           * @Transform
           */
          public function transformStringToProduct(
              string $productSku,
              ProductRepositoryInterface $productRepository,
              ProductFactory $productFactory
          ): ProductInterface {
              Assert::assertInstanceOf(ProductRepositoryInterface::class, $productRepository);

              return $productFactory->create()->setSku($productSku);
          }

          /**
           * @Given a service has been successfully injected to the parameter transformation method while transforming :product
           */
          public function aServiceHasBeenSuccessfullyInjectedToTheParameterTransformationMethodWhileTransforming(ProductInterface $product)
          {
              Assert::assertInstanceOf(ProductInterface::class, $product);
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
            services: '@seec.magento2_extension.service_container'

        extensions:
          SEEC\Behat\Magento2Extension: ~
      """
    When I run Behat
    Then I should see the tests passing
