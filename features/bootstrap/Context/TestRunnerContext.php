<?php

namespace Bex\Behat\Magento2Extension\Acceptance\Context;

use Behat\Gherkin\Node\PyStringNode;
use Bex\Behat\Context\TestRunnerContext as DefaultTestRunnerContext;
use Symfony\Component\Process\Process;

abstract class TestRunnerContext extends DefaultTestRunnerContext
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

    public function createWorkingDirectory()
    {
        parent::createWorkingDirectory();

        $this->filesystem->copy($this->workingDirectory . '/app/etc/config.php', '/tmp/config.php.backup', true);
    }

    public function clearWorkingDirectory()
    {
        parent::clearWorkingDirectory();

        $this->filesystem->copy('/tmp/config.php.backup', $this->workingDirectory . '/app/etc/config.php', true);
        $this->filesystem->remove('/tmp/config.php.backup');
        $this->runMagentoCommand('cache:clear');
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
    }

    /**
     * @Given I have an adminhtml Magento DI configuration in this module:
     */
    public function iHaveAnAdminhtmlMagentoDIConfigurationInThisModule(PyStringNode $content)
    {
        $file = $this->modulePath . '/etc/adminhtml/di.xml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;
    }

    /**
     * @Given I have a test Magento DI configuration in this module:
     */
    public function iHaveATestMagentoDiConfigurationInThisModule(PyStringNode $content)
    {
        $file = $this->modulePath . '/etc/test/di.xml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;
    }

    /**
     * @Given I have the helper service configuration:
     */
    public function iHaveTheHelperServiceConfiguration(PyStringNode $content)
    {
        $file = $this->workingDirectory . '/features/bootstrap/config/services.yml';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;
    }

    /**
     * @Given /^the behat helper service class file "([^"]*)" contains:$/
     */
    public function theBehatHelperServiceClassFileContains(string $className, PyStringNode $content)
    {
        $file = $this->workingDirectory . '/features/bootstrap/' . str_replace('\\', '/', $className) . '.php';

        $this->filesystem->dumpFile($file, $content->getRaw());

        $this->files[] = $file;
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
