Usage Examples
==============

Manually Inject Services to Context
-----------------------------------

You can inject any Magento service into your Behat Contexts like this:

Configuration:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          contexts:
            - YourContext:
              - '@Magento\Catalog\Api\ProductRepositoryInterface'
          services: '@bex.magento2_extension.service_container'

Context:

.. code-block:: php

    <?php

    use Behat\Behat\Context\Context;
    use Magento\Catalog\Api\ProductRepositoryInterface;

    class YourContext implements Context
    {
        /** @var ProductRepositoryInterface */
        private $productRepository;

        public function __construct(ProductRepositoryInterface $productRepository)
        {
            $this->productRepository = $productRepository;
        }
    }

That's all. With the above the Product Repository will be available in your Behat Context. :)


Automatically Inject Services to Context
----------------------------------------

Since this extension only provides a custom service container and it does not override the default Behat argument resolvers you can take advantage of the default `Behat service autowiring feature <https://github.com/Behat/Behat/pull/1071>`_.
You can enable this feature by adding ``autowire: true`` to the behat config of your test suite. After that your configuration will look like this:

.. code-block:: yaml

  default:
    suites:
      yoursuite:
        autowire: true
        
        contexts:
          - YourContext
        
        services: '@bex.magento2_extension.service_container'

Note that the argument resolver is able to autowire services for:
 - constructor arguments
 - step definition arguments
 - transformation arguments
 
For more information see the documentation `here <https://github.com/Behat/Behat/pull/1071>`_.

