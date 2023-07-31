Manually inject service to Behat Context as constructor argument
================================================================

If you didn't enable the Behat autowire feature then you need to provide your Behat Context dependencies manually in the Behat config. E.g.:

**Feature:**

.. code-block:: gherkin

  Feature: Magento and Behat DI connected
    As a developer
    In order to write Behat tests easily
    I should be able to inject services from the Magento DI into Behat Contexts

    Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Context constructor
      Given A service has been successfully injected through the Context constructor
      When I work with Behat
      Then I am happy

**Context:**

.. code-block:: php

    <?php

    use Behat\Behat\Context\Context;
    use Exception;
    use Magento\Catalog\Api\ProductRepositoryInterface;

    class YourContext implements Context
    {
        /** @var ProductRepositoryInterface */
        private $productRepository;

        public function __construct(ProductRepositoryInterface $productRepository)
        {
            $this->productRepository = $productRepository;
        }

        /**
         * @Given A service has been successfully injected through the Context constructor
         */
        public function theProductRepositorySuccessfullyInjectedAsConstructorArgument()
        {
            if (!$this->productRepository instanceof ProductRepositoryInterface) {
                throw new Exception('Something went wrong :(');
            }
        }

        /**
         * @When I work with Behat
         */
        public function iWorkWithBehat()
        {
            // no-op
        }

        /**
         * @Then I am happy
         */
        public function iAmHappy()
        {
            // no-op :)
        }
    }

**Configuration:**

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          contexts:
            - YourContext:
              - '@Magento\Catalog\Api\ProductRepositoryInterface'
          services: '@seec.magento2_extension.service_container'

That's all. With the above the Product Repository will be injected to your Behat Context.
