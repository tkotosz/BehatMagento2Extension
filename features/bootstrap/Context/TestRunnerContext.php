<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

use Behat\Gherkin\Node\PyStringNode;
use Bex\Behat\Context\TestRunnerContext as DefaultTestRunnerContext;
use Symfony\Component\Process\Process;

class TestRunnerContext extends DefaultTestRunnerContext
{
    /** @var string */
    private $modulePath;

    /**
     * @Then I should see the tests passing
     */
    public function iShouldSeeTheTestsPassing()
    {
        $this->iShouldNotSeeAFailingTest();
    }

    /**
     * @Given I have a Magento module called :moduleName
     */
    public function iHaveAMagentoModuleCalled(string $moduleName)
    {
        [$vendor, $module] = explode('_', $moduleName);
        $this->modulePath = $this->workingDirectory . '/app/code/' . $vendor . '/' . $module;
        $registrationFile = $this->modulePath . '/registration.php';
        $moduleFile = $this->modulePath . '/etc/module.xml';

        $registrationFileContent = <<<CONTENT
<?php

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    '$moduleName',
    __DIR__
);

CONTENT;

        $moduleFileContent = <<<CONTENT
<?xml version="1.0"?>

<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="$moduleName" setup_version="1.0.0" />
</config>

CONTENT;

        $this->filesystem->dumpFile($registrationFile, $registrationFileContent);
        $this->filesystem->dumpFile($moduleFile, $moduleFileContent);

        $this->files[] = $registrationFile;
        $this->files[] = $moduleFile;

        $this->runMagentoCommand('module:enable', $moduleName);
    }

    /**
     * @Given I have an interface :fqcn defined in this module:
     * @Given I have a class :fqcn defined in this module:
     */
    public function iHaveAnInterfaceDefinedInThisModule(string $fqcn, PyStringNode $content)
    {
        $file = $this->workingDirectory . '/app/code/' . str_replace('\\', '/', $fqcn) . '.php';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;
    }

    /**
     * @Given I have a global Magento DI configuration in this module:
     */
    public function iHaveAGlobalMagentoDiConfigurationInThisModule(PyStringNode $content)
    {
        $file = $this->modulePath . '/etc/di.xml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;

        $this->runMagentoCommand('cache:clean');
    }

    /**
     * @Given I have a frontend Magento DI configuration in this module:
     */
    public function iHaveAFrontendMagentoDIConfigurationInThisModule(PyStringNode $content)
    {
        $file = $this->modulePath . '/etc/frontend/di.xml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;

        $this->runMagentoCommand('cache:clean');
    }

    /**
     * @Given I have a test Magento DI configuration in this module:
     */
    public function iHaveATestMagentoDiConfigurationInThisModule(PyStringNode $content)
    {
        $file = $this->modulePath . '/etc/test/di.xml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;

        $this->runMagentoCommand('cache:clean');
    }

    protected function runMagentoCommand(string $command, string $arguments = '')
    {
        $magentoProcess = new Process(
            sprintf('%s %s %s', 'bin/magento', $command, !empty($arguments) ? escapeshellarg($arguments) : ''),
            $this->workingDirectory
        );
        $magentoProcess->run();
    }
}
