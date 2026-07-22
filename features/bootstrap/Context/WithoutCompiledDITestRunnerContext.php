<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

class WithoutCompiledDITestRunnerContext extends TestRunnerContext
{
    public function iRunBehat($parameters = '', $phpParameters = '')
    {
        $this->runMagentoCommand('cache:clean');
        parent::iRunBehat($parameters, $phpParameters);
    }
}
