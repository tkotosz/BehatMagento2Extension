<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

class WithoutCompiledDITestRunnerContext extends TestRunnerContext
{
    public function iRunBehat($parameters = '', $phpParameters = '')
    {
        //$this->runMagentoCommand('cache:clear');
        parent::iRunBehat($parameters, $phpParameters);
    }
}