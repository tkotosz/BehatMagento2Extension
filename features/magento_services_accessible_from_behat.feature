Feature: Magento and Behat DI connected
  As a developer
  In order to write Behat tests easily
  I should be able to inject services from the Magento DI into Behat Contexts

  Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Context constructor
    Given A service has been successfully injected through the Context constructor
    When I work with Behat
    Then I am happy

  Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Step
    Given A service has been successfully injected as argument to this step
    When I work with Behat
    Then I am happy

  Scenario: Injecting service from Magento DI to Behat Context as argument for Behat Step Paramater Transformation method
    Given A service has been successfully injected to the parameter transformation method while transforming "foobar"
    When I work with Behat
    Then I am happy