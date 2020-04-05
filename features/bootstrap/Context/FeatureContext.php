<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Bex\Behat\Context\TestRunnerContext;

class FeatureContext implements Context
{
    /** @var TestRunnerContext */
    private $testRunnerContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->testRunnerContext = $scope->getEnvironment()->getContext(TestRunnerContext::class);
    }

    /**
     * @Then I should see the tests passing
     */
    public function iShouldSeeTheTestsPassing()
    {
        $this->testRunnerContext->iShouldNotSeeAFailingTest();
    }
}
