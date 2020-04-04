Configuration
=============

Basic Configuration
-------------------

1. Activate the extension in your ``behat.yml``:

    .. code-block:: yaml

        default:
          extensions:
            Bex\Behat\Magento2Extension: ~

2. Configure the Magento2 Behat Service Container for your test suite:

    .. code-block:: yaml

        default:
          suites:
            yoursuite:
              services: '@bex.magento2_extension.service_container'

With the above configuration Behat will use the service container provided by this extension which makes all services defined in the Magento 2 DI available to inject into any Context.

Configure the Magento bootstrap path
------------------------------------

If your Magento ``bootstrap.php`` is not available in the default ``app/bootstrap.php`` location then you can specify the custom path in the following way:

.. code-block:: yaml

    default:
      extensions:
        Bex\Behat\Magento2Extension:
          bootstrap: path/to/your/bootstrap.php # by default app/bootstrap.php

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