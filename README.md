BehatMagento2Extension
=========================

The `BehatMagento2Extension` provides a custom service container for Behat which allows to inject Magento services into Behat Contexts and Behat helper services.

Installation
------------

The recommended installation method is through [Composer](https://getcomposer.org):

```bash
composer require --dev bex/behat-magento2-extension
```

Basic Configuration
-------------------

1. Enable the extension in the `behat.yml`:

```yml
default:
  extensions:
    Bex\Behat\Magento2Extension: ~
```

2. Configure the Magento2 Behat Service Container for your test suite:

```yml
default:
  suites:
    yoursuite:
      services: '@bex.magento2_extension.service_container'
```

With the above configuration Behat will use the service container provided by this extension which makes all services defined in the Magento 2 DI available to inject into any Context.

Basic Usage
-----------

You can inject any Magento service into your Behat Contexts like this:

1. Config:

```yml
default:
  suites:
    yoursuite:
      contexts:
        - YourContext:
          - '@Magento\Catalog\Api\ProductRepositoryInterface'
      services: '@bex.magento2_extension.service_container'
```

2. Context:

```php
<?php

use Behat\Behat\Context\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;

class FirstContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }
}
```

That's all. With the above the Product Repository will be available in your Behat Context. :)

Use Behat Helper Services
-------------------------

If you are familiar with the [helper container](https://github.com/Behat/Behat/pull/974) feature in Behat then probably you already got used to defining helper services under the `services` configuration key like this:

```yml
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
```

Unfortunately the custom service container is registered under the same key (see Basic Configuration section) so we are not able to specify our helper services here.
But don't worry this extension allows you to register your helper services in a custom Symfony DI container in the following way:

1. Configure the path for the service container configuration file:

```yml
default:
  extensions:
    Bex\Behat\Magento2Extension:
      services: features/bootstrap/config/services.yml
```
Note: You can use `yml`, `xml` or `php` format. For more information see the official documentation of the [Symfony DI component](https://symfony.com/doc/current/components/dependency_injection.html).

2. Define your helper service:

```yml
services:
  SharedService: ~
```

3. Inject your helper service into your Behat Context:

```yml
default:
  suites:
    yoursuite:
      contexts:
        - YourContext:
          - '@Magento\Catalog\Api\ProductRepositoryInterface'
          - '@SharedService'
      services: '@bex.magento2_extension.service_container'
```

4. Update your Behat Context:

```php
<?php

use Behat\Behat\Context\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Toom\Acceptance\Service\SharedService;

class FirstContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;
    
    /** @var SharedService */
    private $sharedService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SharedService $sharedService
    ) {
        $this->productRepository = $productRepository;
        $this->sharedService = $sharedService;
    }
}
```

That's all. Now your helper service should be successfully injected to your Behat Context. :)

Inject dependencies to helper services
--------------------------------------

Since the helper services are defined in a custom Symfony DI container it is possible to pass over dependencies to your helper services.
You can simply do this in the following way:

```yml
services:
  AnotherSharedService: ~

  SharedService:
    arguments: ['@AnotherSharedService']
```

In addition to this the extension gives you access to any service defined in the default Behat service container or in the Magento DI.
Which means you can inject any service defined by the Behat application itself or by any Behat extension or by Magento into your helper services.

1. Example 1 - Inject from Magento DI:

```yml
services:
  AnotherSharedService: ~

  SharedService:
    arguments:
      - '@AnotherSharedService'
      - '@Magento\Sales\Api\OrderRepositoryInterface'
```

2. Example 2 - Inject service from Behat service container:

Let's say you have the [Mink extension](https://packagist.org/packages/behat/mink-extension) installed. In this case we know that the extension defines a `@mink` service so we use it!

```yml
services:
  AnotherSharedService: ~

  SharedService:
    arguments:
      - '@AnotherSharedService'
      - '@Magento\Sales\Api\OrderRepositoryInterface'
      - '@mink'
```

3. Example 3 - Inject parameter from Behat service container:

```yml
services:
  AnotherSharedService: ~

  SharedService:
    arguments:
      - '@AnotherSharedService'
      - '@Magento\Sales\Api\OrderRepositoryInterface'
      - '@mink'
      - '%paths.base%'
```

```php
<?php

use Behat\Mink\Mink;
use Magento\Sales\Api\OrderRepositoryInterface;

class SharedService
{
    /** @var AnotherSharedService */
    private $anotherSharedService;

    /** @var OrderRepositoryInterface */
    private $orderRepository;
    
    /** @var Mink */
    private $mink;
    
    /** @var string */
    private $basePath;

    public function __construct(
        AnotherSharedService $anotherSharedService,
        OrderRepositoryInterface $orderRepository,
        Mink $mink,
        string $basePath
    ) {
        $this->anotherSharedService = $anotherSharedService;
        $this->orderRepository = $orderRepository;
        $this->mink = $mink;
        $this->basePath = $basePath;
    }
}
```

These are all the options available for you. Note that in the same way as in `Example 3` you can inject these services directly to the Contexts as well.

Autowire Context arguments
--------------------------

Since this extension only provides a custom service container and it does not override the default Behat argument resolvers you can take advantage of the default [Behat service autowiring feature](https://github.com/Behat/Behat/pull/1071).
You can enable this feature by adding `autowire: true` to the behat config of your test suite. After that your configuration will look like this:

```yml
default:
  suites:
    yoursuite:
      autowire: true
      
      contexts:
        - YourContext
      
      services: '@bex.magento2_extension.service_container'
```

Note that the argument resolver is able to autowire services for:
 - constructor arguments
 - step definition arguments
 - transformation arguments
 
For more information see the documentation [here](https://github.com/Behat/Behat/pull/1071).

Autowire arguments for helper services
--------------------------------------

The helper services are defined in the custom Symfony DI container so we can take advantage of the autowire feature of the Symfony DI component as well.
You can enable this feature by adding the `autowire: true` configuration to your service container configuration. After that your configuration will look like this:

```yml
services:
  _defaults:
    autowire: true

  AnotherSharedService: ~

  SharedService:
    arguments:
      $mink: '@mink'
      $basePath: '%paths.base%'
```

As you can see all injectable service argument removed now. But we still need to specify 2 arguments:
- Mink service cannot be autowired because the service id is not the [FQCN](https://acronyms.thefreedictionary.com/FQCN)
- Base Path cannot be autowired since it is a string parameter

Additional configuration options 
--------------------------------

### Configure the Magento bootstrap path

If your Magento `bootstrap.php` is not available in the default `app/bootstrap.php` location then you can specify the custom path in the following way:

```yml
default:
  extensions:
    Bex\Behat\Magento2Extension:
      bootstrap: path/to/your/bootstrap.php # by default app/bootstrap.php
```

### Configure the Magento area

Services in the Magento DI can be defined on global level (in any module's `etc/di.xml`) but you can also define and/or override services for a specific Magento area.
When testing your feature you might want to access services defined for a specific area so in order to support this the extension provides an additional config option which you can change per test suite.
You can configure the required area in the following way:

```yml
default:
  suites:
    yoursuite:
      autowire: true
      
      contexts:
        - YourContext
      
      services: '@bex.magento2_extension.service_container'
      
      magento:
        area: adminhtml
```

This will tell the extension to load the services from the `adminhtml` area.
Note that by default only the `global` area services are loaded. When specifying an area in the config you will always get all services from the `global` area extended by the specific configured area. For example in the above case you will get all the services from the `global` area overridden/extended by the `adminhtml` area.

### Mocking dependencies

When you test your feature you might want to mock some services to e.g. avoid using external services like database, cache, etc. in your domain tests.

In order to achieve this we can use a custom Magento area where we can easily replace dependencies during our test run.

To do this we need to 2 things:
1. Configure a new test area in Magento
2. Define our custom DI configuration
3. Configure this test area in our Behat suite configuration

The first can be done by defining the custom area in our module's `etc/di.xml` in the following way:
```xml
<?xml version="1.0" encoding="utf-8"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Magento\Framework\App\AreaList">
        <arguments>
            <argument name="areas" xsi:type="array">
                <item name="my_feature_test" xsi:type="null" />
            </argument>
        </arguments>
    </type>
</config>
```

Note: Don't forget to clear the Magento cache to reload the available are codes.

Then we can freely change and DI configuration in our module's `etc/my_feature_test/di.xml`.

For example lets say we have a service like this in our module:

```php
<?php

namespace Vendor\ModuleName\Provider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderProvider
{
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @param SearchCriteriaBuilder    $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder, OrderRepositoryInterface $orderRepository)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string $orderIncrementId
     *
     * @return OrderInterface|null
     */
    public function getOrderByIncrementId(string $orderIncrementId): ?OrderInterface
    {
        $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $orderIncrementId);
        $searchResults = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        if ($searchResults->getTotalCount() == 0) {
            return null;
        }

        $items = $searchResults->getItems();

        return array_shift($items);
    }
}
```

As you can see this service uses the Order Repository service to load orders from the database. So we can simply replace this argument with a mock to return test orders from in-memory.
To do this first we can create a mock service like this:

```php
<?php

namespace Vendor\ModuleName\Test;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class FakeOrderRepository implements OrderRepositoryInterface
{
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        throw new Exception('TODO: Implement getList() method.');
    }

    public function get($id)
    {
        throw new Exception('TODO: Implement get() method.');
    }

    public function delete(OrderInterface $entity)
    {
        throw new Exception('TODO: Implement delete() method.');
    }

    public function save(OrderInterface $entity)
    {
        throw new Exception('TODO: Implement save() method.');
    }
}
```

Then we can register it in our test area in the `etc/my_feature_test/di.xml`:
```xml
<?xml version="1.0" encoding="utf-8"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Vendor\ModuleName\Provider\OrderProvider">
        <arguments>
            <argument name="orderRepository" xsi:type="object">Vendor\ModuleName\Test\FakeOrderRepository</argument>
        </arguments>
    </type>
</config>
```

The only thing left is to configure our suite to use this custom area:

```yml
default:
  suites:
    yoursuite:
      autowire: true
      
      contexts:
        - YourContext
      
      services: '@bex.magento2_extension.service_container'
      
      magento:
        area: my_feature_test
```

And that's all. If you inject a service into you Context which uses the `OrderProvider` or inject the `OrderProvider` itself then the `FakeOrderRepository` will be used as its dependency instead of the default `OrderRepository`.

Notes:

1. Defining the test area:

If you don't want to specify a test area specifically for your module then you can install the [Test area](https://packagist.org/packages/tkotosz/test-area-magento2) Magento 2 module which will define an area called `test` for you, so you can do the di overrides in your module's `etc/test/di.xml`.

2. Extending a base area:

By default all area extends the `global` area, but you might want to use another area as your base area. This can be configured in the behat config:

For example if you would like to use the `adminhtml` area and just override some services from that area, then you can configure the following in behat:

```yml
default:
  suites:
    yoursuite:
      autowire: true
      
      contexts:
        - YourContext
      
      services: '@bex.magento2_extension.service_container'
      
      magento:
        area: [adminhtml, my_feature_test]
```

The extension will take care of the loading and merging of the service configurations of these areas in the provided order. So in the above example the following will happen:
1. `global` area is loaded
2. `adminhtml` area is loaded and overrides services / adds new services
3. `my_feature_test` area is loaded and overrides services / adds new services

