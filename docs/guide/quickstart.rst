Quick start
===========

Install Behat
-------------

If you didn't install Behat already, then you can install it with composer in the following way:

  .. code-block:: bash

      $ composer require --dev behat/behat

For alternative installation options check the `Behat official documentation <https://docs.behat.org/en/latest/quick_start.html#installation>`_

Install the Extension
---------------------

Similarly you can install the extension via composer:

  .. code-block:: bash

      $ composer require --dev bex/behat-magento2-extension

For more information see the the :doc:`installation section of this documentation </guide/installation>`.

Setup the Behat configuration
-----------------------------

You need to enable the extension in the Behat configuration and configure your Behat Suite to use the Magento 2 Service Container. Your ``behat.yml`` should look like this:

    .. code-block:: yaml

        default:
          extensions:
            Bex\Behat\Magento2Extension: ~

          suites:
            application:
              autowire: true

              contexts:
                - FeatureContext

              services: '@bex.magento2_extension.service_container'

With the above configuration:
 - The extension is enabled
 - The ``application`` suite uses the Behat Magento 2 service container
 - The Behat Context dependencies are autowired

For more detailed information see the :doc:`configuration section of this documentation </guide/configuration>`.

Verify the configuration
------------------------

In order to verify that the extension is configured correctly you will need a test feature. For example create a ``features/my_feature.feature`` file like this:

    .. code-block:: gherkin

        Feature: Magento and Behat DI connected
          As a developer
          In order to write Behat tests easily
          I should be able to inject services from the Magento DI into Behat Contexts

          Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Context constructor
            Given A service has been successfully injected through the Context constructor
            When I work with Behat
            Then I am happy

Also to implement the above feature you need to add the following step definitions to your ``features/bootstrap/FeatureContext.php`` Behat Context:

    .. code-block:: php

        <?php

        use Behat\Behat\Context\Context;
        use Exception;
        use Magento\Sales\Api\OrderRepositoryInterface;

        class FeatureContext implements Context
        {
            /** @var OrderRepositoryInterface */
            private $orderRepository;

            public function __construct(OrderRepositoryInterface $orderRepository)
            {
                $this->orderRepository = $orderRepository;
            }

            /**
             * @Given A service has been successfully injected through the Context constructor
             */
            public function aServiceHasBeenSuccessfullyInjectedThroughTheContextConstructor()
            {
                if (!$this->orderRepository instanceof OrderRepositoryInterface) {
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

Note that here we inject the Order Repository Magento service through the Context constructor, but it is also possible to inject it through the Behat Step definition as well. For more information see the :doc:`usage section of this documentation </guide/usage>`.

Run Behat and you should see the test passing.

    .. code-block:: bash

        $ bin/behat features/my_feature.feature
