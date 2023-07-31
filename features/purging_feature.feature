@purge @fixtureCreation
Feature: Using helper services to access services outside of Magento
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from an additional helper service container

  Background:
    Given I have the context:
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
              - SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Hook\DatabaseHook
              - SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Customer\CustomerContext
              - SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Store\StoreContext
              - SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\Stock\StockContext
            services: '@seec.magento2_extension.service_container'

        extensions:
          SEEC\Behat\Magento2Extension:
            services: features/bootstrap/config/services.yml
      """

  Scenario: I run my custom tests and then I expect the database to be empty afterwards
    Given I have the feature:
      """
      Feature: My awesome purging hook feature
      Scenario: Run purged scenario, create a customer and persist it
        Given a helper service has been successfully injected as argument to this step
        And the application has a default stock entity
        And the application has a stock entity with name "Some other test"
        And a frontend store-view exists
        And I have a customer with this data:
          | email     | test@test.de |
          | password  | test123!     |
          | firstname | John         |
          | lastname  | Doe          |
        Then there is 1 customer existing
      Scenario: Run once again, now the database should be cleaned of any traces and only contain default fixtures
        Given there are 0 customers existing
      """
    When I run Behat
    Then I should not see a failing test
