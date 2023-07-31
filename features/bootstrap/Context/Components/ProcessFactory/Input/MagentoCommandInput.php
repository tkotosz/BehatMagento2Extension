<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\ProcessFactory\Input;

use SEEC\BehatTestRunner\Context\Components\ProcessFactory\Input\AbstractInput;
use Symfony\Component\Process\PhpExecutableFinder;

final class MagentoCommandInput extends AbstractInput
{
    public function __construct(
        string $command = null,
        string $commandParameters = null,
        string $workingDirectory = null
    ) {
        $this->setExecutor((new PhpExecutableFinder())->find() ?: null);
        $this->setExecutorParameters('-dmemory_limit=-1');
        $this->setCommand('bin/magento');
        $this->setParameters($command);
        $this->setExtraParameters($commandParameters);
        $this->setDirectory($workingDirectory);
    }
}
