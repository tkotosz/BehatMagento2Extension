Inject service to Behat Context as Behat Step argument
======================================================

The `Behat service autowiring feature <https://github.com/Behat/Behat/pull/1071>`_ allows to inject services from the configured service container to any of the Step Definitions as argument. You can use this feature in combination with this extension as well. E.g.:

**Feature:**

.. code-block:: gherkin

  Feature: Magento and Behat DI connected
    As a developer
    In order to write Behat tests easily
    I should be able to inject services from the Magento DI into Behat Contexts

    Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Step
      Given A service has been successfully injected as argument to this step
      When I work with Behat
      Then I am happy

**Context:**

.. code-block:: php

    <?php

    use Behat\Behat\Context\Context;
    use Magento\Catalog\Api\ProductRepositoryInterface;

    class YourContext implements Context
    {
        /**
         * @Given A service has been successfully injected as argument to this step
         */
        public function theProductRepositorySuccessfullyInjectedAsArgument(ProductRepositoryInterface $productRepository)
        {
            if (!$this->productRepository instanceof ProductRepositoryInterface) {
                throw new Exception('Something went wrong :(');
            }
        }
    }

**Configuration:**

.. code-block:: yaml

  default:
    suites:
      yoursuite:
        autowire: true
        
        contexts:
          - YourContext
        
        services: '@bex.magento2_extension.service_container'
