Configuration
=============

Enable the Extension
--------------------

You can enable the extension in your ``behat.yml`` in following way:

.. code-block:: yaml

    default:
      extensions:
        Bex\Behat\Magento2Extension: ~

Configure the Service Container
-------------------------------
In order to be able to access the Magento 2 services from your Behat Contexts you need to configure the Magento2 Behat Service Container for your test suite. You can do it like this:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          services: '@bex.magento2_extension.service_container'

With the above configuration Behat will use the service container provided by this extension which makes all services defined in the Magento 2 DI available to inject into any Context.

Note that you need to pass over the dependencies to your Contexts manually like this:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          contexts:
            - YourContext:
              - '@Magento\Catalog\Api\ProductRepositoryInterface'
          
          services: '@bex.magento2_extension.service_container'

Enable Autowiring for Contexts
------------------------------

This extension does not override the default Behat argument resolvers. Because of this you can take advantage of the default `Behat service autowiring feature <https://github.com/Behat/Behat/pull/1071>`_.
You can enable this feature by adding ``autowire: true`` to the behat config of your test suite. After that services from Magento will be automatically injected to the Contexts without any manual configuration.

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
 
For more information see the :doc:`usage examples section of this documentation </guide/usage>`.

Configure the Magento area
--------------------------

Services in the Magento DI can be defined on global level (in any module's ``etc/di.xml``) but you can also define and/or override services for a specific Magento area (e.g. ``etc/frontend/di.xml``).
When testing your feature you might want to access services defined for a specific area so in order to support this the extension provides an additional config option which you can change per test suite.
You can configure the required area in the following way:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          contexts:
            - YourContext
          
          services: '@bex.magento2_extension.service_container'
          
          magento:
            area: adminhtml

This will tell the extension to load the services from the ``adminhtml`` area.
Note that by default only the ``global`` area services are loaded. When specifying an area in the config you will always get all services from the ``global`` area extended by the specific configured area. For example in the above case you will get all the services from the ``global`` area overridden/extended by the ``adminhtml`` area.

If you have a ``test`` area (see Configure Magento DI overrides section) then you can configure it here as area. Also if you would like to use a built-in area in combination with the test area then you can configure it like this:

.. code-block:: yaml

    magento:
      area: [adminhtml, test]

The extension will take care of the loading and merging of the service configurations of these areas in the provided order. So in the above example the following will happen:

1. ``global`` area is loaded

2. ``adminhtml`` area is loaded and overrides services / adds new services

3. ``test`` area is loaded and overrides services / adds new services

Configure Magento DI overrides
------------------------------

When you test your feature you might want to mock some services to e.g. avoid using external services like database, cache, etc. during your test run.

In order to achieve this we can use a custom Magento area where we can easily replace dependencies.

To do this we need to do 3 things:

1. Configure a new test area in Magento

This can be done by defining the custom area in your module's ``etc/di.xml`` in the following way:

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

Alternatively you can install the `Test area Magento 2 module <https://packagist.org/packages/tkotosz/test-area-magento2>`_ which will define an area called ``test`` for you, so you can do the di overrides in your module's ``etc/test/di.xml``.

Note: Don't forget to clear the Magento cache to reload the available area codes.

2. Define custom DI configuration in that area

Since the ``test`` area now exsits as a valid area code in Magento, you can freely change any DI configuration in your module's `etc/test/di.xml`. E.g.:

.. code-block:: xml

    <?xml version="1.0"?>
    <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
        <preference for="Magento\Catalog\Api\ProductRepositoryInterface" type="Foo\Bar\Test\FakeProductRepository" />
    </config>

3. Configure this test area in the Behat suite configuration

In order to load this custom DI configuration during the test run the test area need to be configured in the Behat test suite so it can load to merge it with the default area.

.. code-block:: yml

    default:
      suites:
        yoursuite:
          autowire: true
          
          contexts:
            - YourContext
          
          services: '@bex.magento2_extension.service_container'
          
          magento:
            area: test

Note that the above configuration will only load services from the ``global`` and ``test`` areas. If you would like to load services from another area as well (e.g. ``adminhtml``) then you can specify the a list of area codes as parameter for the ``area`` config option. For more information see the "Configure the Magento area" section above.

And that's all. If you inject a service into you Context which uses the ``ProductRepositoryInterface`` or inject the ``ProductRepositoryInterface`` itself then the ``FakeProductRepository`` will be used as its dependency instead of the default ``ProductRepository``.

Configure Behat Helper Services
-------------------------------

If you are familiar with the `helper container feature <https://github.com/Behat/Behat/pull/974>`_ in Behat then probably you already got used to defining helper services under the ``services`` configuration key like this:

.. code-block:: yaml

    default:
      suites:
        default:
          contexts:
            - FirstContext:
              - "@SharedService"
            - SecondContext:
              - "@SharedService"

          services:
            SharedService: ~

Unfortunately the custom service container is registered under the same key (see Configure the Service Container section) so we are not able to specify our helper services here.
But don't worry this extension allows you to register your helper services in a custom Symfony DI container in the following way:

1. Configure the path for the service container configuration file:
    .. code-block:: yaml

        default:
          extensions:
            Bex\Behat\Magento2Extension:
              services: features/bootstrap/config/services.yml

Note: You can use ``yml``, ``xml`` or ``php`` format. For more information see the official documentation of the `Symfony DI component <https://symfony.com/doc/current/components/dependency_injection.html>`_.

2. Define your helper service:

Define your helper services in the servies configuration file which you created in the first step.

.. code-block:: yaml

    services:
      _defaults:
        public: true
      
      SharedService: ~

3. Inject your helper service into your Behat Context:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          contexts:
            - YourContext:
              - '@Magento\Catalog\Api\ProductRepositoryInterface'
              - '@SharedService'
          services: '@bex.magento2_extension.service_container'

Alternatively if you are using autowiring (see Enable Autowiring for Contexts section) then you can skip this step since the context arguments will be autowired even from this custom Symfony service container.

That's all. Now your helper service should be successfully injected to your Behat Context.

Inject dependencies to helper services
--------------------------------------

Since the helper services are defined in a custom Symfony DI container (see Configure Behat Helper Services section) it is possible to pass over dependencies to your helper services.
You can simply do this in the following way:

.. code-block:: yaml

    services:
      _defaults:
        public: true
        
      AnotherSharedService: ~

      SharedService:
        arguments: ['@AnotherSharedService']

In addition to this the extension gives you access to any service defined in the default Behat service container or in the Magento DI. Which means you can inject any service defined by the Behat application itself or by any Behat extension or by Magento into your helper services.

.. code-block:: yaml

    services:
      _defaults:
        public: true
        
      AnotherSharedService: ~

      SharedService:
        arguments:
          - '@AnotherSharedService'
          - '@Magento\Sales\Api\OrderRepositoryInterface'
          - '@mink'
          - '%paths.base%'

In the above example we injected services from various places:
- ``@AnotherSharedService`` injected from the helper service container
- ``@Magento\Sales\Api\OrderRepositoryInterface`` injected from the Magento DI
- ``@mink`` injected from the `MinkExtension <https://packagist.org/packages/behat/mink-extension>`_ (this example only works if you have the MinkExtension extension installed)
- ``%paths.base%`` injected from the Behat built-in service container

Enable Autowiring for helper services
--------------------------------------

The helper services are defined in the custom Symfony DI container (see Configure Behat Helper Services section) so we can take advantage of the autowire feature of the Symfony DI component as well.
You can enable this feature by adding the ``autowire: true`` configuration to your service container configuration.

.. code-block:: yaml

    services:
      _defaults:
        public: true
        autowire: true

      AnotherSharedService: ~

      SharedService:
        arguments:
          $mink: '@mink'
          $basePath: '%paths.base%'

As you can see all injectable service argument omitted. But we still need to specify 2 arguments:
- Mink service cannot be autowired because the service id is not the `FQCN <https://acronyms.thefreedictionary.com/FQCN>`_
- Base Path cannot be autowired since it is a string parameter

Configure the Magento bootstrap path
------------------------------------

If your Magento ``bootstrap.php`` is not available in the default ``app/bootstrap.php`` location then you can specify the custom path in the following way:

.. code-block:: yaml

    default:
      extensions:
        Bex\Behat\Magento2Extension:
          bootstrap: path/to/your/bootstrap.php # by default app/bootstrap.php