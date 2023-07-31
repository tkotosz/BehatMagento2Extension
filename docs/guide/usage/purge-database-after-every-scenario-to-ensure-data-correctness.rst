Purge the Database after each scenario
======================================

It can be beneficial to purge the database after each scenario in order to work with the data you want, which will
allow you correct test cases in the long run. Please be aware that this will totally truncate each table. Do not use this feature on any kind of
production application, as it will completely wipe your database and will add fixture data to it.

You can make usage of the Database Hook with the following `behat.yml` configuration:

.. code-block:: yaml

    default:
      suites:
        yoursuite:
          autowire: true

          contexts:
            - YourContext
            - SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Hook\DatabaseHook

          services: '@seec.magento2_extension.service_container'

With this Hook, Behat will always purge the database before you run a scenario. There are some exceptions to it, as not all
tables are purged to ensure that the Behat Test suite can continue and run properly. The following tables are not purged by the code:

.. code-block:: php

        $purger->purge($connection, [
            'core_config_data',
            'eav_attribute',
            'eav_attribute_group',
            'eav_attribute_label',
            'eav_attribute_option',
            'eav_attribute_option_swatch',
            'eav_attribute_option_value',
            'eav_attribute_set',
            'eav_entity_type',
        ]);

Also, the hook will automatically create a default Stock, Website and Store Group and Store View for you. This is necessary
to use various Repositories throughout the testing scenarios, as you can inject them into your Contexts. It may still
happen that various Repositories do not want to get autowired or injected, but you can use always the `ObjectManager` to
get new classes. In Magento2 development, this is strictly discouraged, but for testing purposes its the right way to go;
in the end we want fresh or singleton instances of classes, which are not affected by other tests.

However: if you see yourself in a situation where you control a class yourself that does not want to get autowired, consider rewriting the class
rather than use the `ObjectManager`.
