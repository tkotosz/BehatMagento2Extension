<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

class DeveloperModeTestRunnerContext extends BaseTestRunnerContext
{
    public function iRunBehat($parameters = '', $phpParameters = '')
    {
        $this->runMagentoCommand('deploy:mode:set', 'developer');
        parent::iRunBehat($parameters, $phpParameters);
    }
}