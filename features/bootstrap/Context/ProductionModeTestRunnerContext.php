<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

class ProductionModeTestRunnerContext extends BaseTestRunnerContext
{
    public function iRunBehat($parameters = '', $phpParameters = '')
    {
        $this->runMagentoCommand('deploy:mode:set', 'production');
        parent::iRunBehat($parameters, $phpParameters);
    }
}