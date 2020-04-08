Feature: Using helper services to access services outside of Magento
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from an additional helper service container

  Scenario: Inject simple helper service to Context
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a helper service has been successfully injected as argument to this step
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use PHPUnit\Framework\Assert;
      use SharedService;

      class FeatureContext implements Context
      {
          /**
           * @Given a helper service has been successfully injected as argument to this step
           */
          public function aHelperServiceHasBeenSuccessfullyInjectedAsArgumentToThisStep(SharedService $sharedService)
          {
              Assert::assertInstanceOf(SharedService::class, $sharedService);
              Assert::assertEquals('foo', $sharedService->foo());
          }
      }
      """
    And the behat helper service class file "SharedService" contains:
      """
      <?php

      class SharedService
      {
          public function foo(): string
          {
              return 'foo';
          }
      }
      """
    And I have the helper service configuration:
      """
      services:
        _defaults:
          public: true

        SharedService:
          class: SharedService
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
          Bex\Behat\Magento2Extension:
            services: features/bootstrap/config/services.yml
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Inject dependencies to helper services
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a helper service has been successfully injected as argument to this step
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use PHPUnit\Framework\Assert;
      use SharedService;
      use Behat\Mink\Mink;
      use Magento\Sales\Api\OrderRepositoryInterface;

      class FeatureContext implements Context
      {
          /**
           * @Given a helper service has been successfully injected as argument to this step
           */
          public function aHelperServiceHasBeenSuccessfullyInjectedAsArgumentToThisStep(SharedService $sharedService)
          {
              Assert::assertInstanceOf(SharedService::class, $sharedService);
              Assert::assertInstanceOf(AnotherSharedService::class, $sharedService->another());
              Assert::assertInstanceOf(OrderRepositoryInterface::class, $sharedService->orderRepository());
              Assert::assertInstanceOf(Mink::class, $sharedService->mink());
              Assert::assertNotEmpty($sharedService->basePath());
          }
      }
      """
    And the behat helper service class file "SharedService" contains:
      """
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

          public function another(): AnotherSharedService
          {
              return $this->anotherSharedService;
          }

          public function orderRepository(): OrderRepositoryInterface
          {
              return $this->orderRepository;
          }

          public function mink(): Mink
          {
              return $this->mink;
          }

          public function basePath(): string
          {
              return $this->basePath;
          }
      }
      """
    And the behat helper service class file "AnotherSharedService" contains:
      """
      <?php

      class AnotherSharedService
      {
          public function bar(): string
          {
              return 'bar';
          }
      }
      """
    And I have the helper service configuration:
      """
      services:
        _defaults:
          public: true

        AnotherSharedService:
          class: AnotherSharedService

        SharedService:
          class: SharedService
          arguments:
            - '@AnotherSharedService'
            - '@Magento\Sales\Api\OrderRepositoryInterface'
            - '@mink'
            - '%paths.base%'
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
          Bex\Behat\Magento2Extension:
            services: features/bootstrap/config/services.yml
          Behat\MinkExtension:
            base_url:  'http://example.com'
            sessions:
              default:
                goutte: ~
      """
    When I run Behat
    Then I should see the tests passing

  Scenario: Autowire helper service dependencies
    Given I have the feature:
      """
      Feature: My awesome feature
      Scenario:
        Given a helper service has been successfully injected as argument to this step
      """
    And I have the context:
      """
      <?php

      use Behat\Behat\Context\Context;
      use PHPUnit\Framework\Assert;
      use SharedService;
      use Behat\Mink\Mink;
      use Magento\Sales\Api\OrderRepositoryInterface;

      class FeatureContext implements Context
      {
          /**
           * @Given a helper service has been successfully injected as argument to this step
           */
          public function aHelperServiceHasBeenSuccessfullyInjectedAsArgumentToThisStep(SharedService $sharedService)
          {
              Assert::assertInstanceOf(SharedService::class, $sharedService);
              Assert::assertInstanceOf(AnotherSharedService::class, $sharedService->another());
              Assert::assertInstanceOf(OrderRepositoryInterface::class, $sharedService->orderRepository());
              Assert::assertInstanceOf(Mink::class, $sharedService->mink());
              Assert::assertNotEmpty($sharedService->basePath());
          }
      }
      """
    And the behat helper service class file "SharedService" contains:
      """
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

          public function another(): AnotherSharedService
          {
              return $this->anotherSharedService;
          }

          public function orderRepository(): OrderRepositoryInterface
          {
              return $this->orderRepository;
          }

          public function mink(): Mink
          {
              return $this->mink;
          }

          public function basePath(): string
          {
              return $this->basePath;
          }
      }
      """
    And the behat helper service class file "AnotherSharedService" contains:
      """
      <?php

      class AnotherSharedService
      {
          public function bar(): string
          {
              return 'bar';
          }
      }
      """
    And I have the helper service configuration:
      """
      services:
        _defaults:
          public: true
          autowire: true

        AnotherSharedService:
          class: AnotherSharedService

        SharedService:
          class: SharedService
          arguments:
            $mink: '@mink'
            $basePath: '%paths.base%'
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
          Bex\Behat\Magento2Extension:
            services: features/bootstrap/config/services.yml
          Behat\MinkExtension:
            base_url:  'http://example.com'
            sessions:
              default:
                goutte: ~
      """
    When I run Behat
    Then I should see the tests passing
