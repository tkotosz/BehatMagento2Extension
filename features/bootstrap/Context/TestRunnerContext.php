<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

use Bex\Behat\Context\TestRunnerContext as DefaultTestRunnerContext;

class TestRunnerContext extends DefaultTestRunnerContext
{
    /**
     * @Then I should see the tests passing
     */
    public function iShouldSeeTheTestsPassing()
    {
        $this->iShouldNotSeeAFailingTest();
    }
}