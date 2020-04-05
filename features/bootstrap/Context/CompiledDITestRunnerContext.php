<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

class CompiledDITestRunnerContext extends TestRunnerContext
{
    public function iRunBehat($parameters = '', $phpParameters = '')
    {
        $this->runMagentoCommand('setup:di:compile');
        parent::iRunBehat($parameters, $phpParameters);
    }
}
