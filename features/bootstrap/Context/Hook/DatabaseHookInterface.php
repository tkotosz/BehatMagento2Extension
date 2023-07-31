<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Hook;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

interface DatabaseHookInterface
{
    public function purgeAndPrefillWithFixtures(BeforeScenarioScope $scope): void;
}
