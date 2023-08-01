<?php

declare(strict_types=1);

namespace SEEC\Behat\Magento2Extension\Features\Bootstrap\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Components\ProcessFactory\Input\MagentoCommandInput;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleaner;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\CacheCleanerInterface;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProvider;
use SEEC\Behat\Magento2Extension\Features\Bootstrap\Context\Tasks\MagentoPathProviderInterface;
use SEEC\BehatTestRunner\Context\AbstractTestRunnerContext;
use SEEC\BehatTestRunner\Context\Components\ProcessFactory\Factory\ProcessFactory;
use SEEC\BehatTestRunner\Context\Components\ProcessFactory\Factory\ProcessFactoryInterface;
use SEEC\BehatTestRunner\Context\Services\WorkingDirectoryServiceInterface;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;

final class TestRunnerContext extends AbstractTestRunnerContext implements Context
{
    private ?string $modulePath = null;

    private ?string $magentoRootDirectory = null;

    private bool $isFreshWorkingDirectory = false;

    private Filesystem $filesystem;

    private ProcessFactoryInterface $processFactory;

    private MagentoPathProviderInterface $magentoPathProvider;

    private CacheCleanerInterface $cacheCleaner;

    public function __construct(
        ?Filesystem $fileSystem = null,
        ?ProcessFactoryInterface $processFactory = null,
        ?WorkingDirectoryServiceInterface $workingDirectoryService = null,
        ?MagentoPathProviderInterface $magentoPathProvider = null,
        ?CacheCleaner $cacheCleaner = null,
        ?string $workingDirectory = null,
    ) {
        $this->filesystem = $fileSystem ?: new Filesystem();
        $this->processFactory = $processFactory ?: new ProcessFactory();
        $this->magentoPathProvider = $magentoPathProvider ?? new MagentoPathProvider();
        $this->cacheCleaner = $cacheCleaner ?? new CacheCleaner($this->magentoPathProvider, $this->filesystem);
        parent::__construct($fileSystem, $processFactory, $workingDirectoryService, $workingDirectory);
    }

    public function createWorkingDirectory(): void
    {
        $this->determineFreshWorkingDirectoryFlag();
        parent::createWorkingDirectory();

        $this->filesystem->copy(
            sprintf('%s/app/etc/config.php', $this->getMagentoRootDirectory()),
            '/tmp/config.php.backup',
            true,
        );
    }

    public function clearWorkingDirectory(): void
    {
        parent::clearWorkingDirectory();
        $this->removeEmptyWorkingDirectory();
        $this->removeModuleFolders();
        $this->revertMagentoConfig();
    }

    private function setModulePath(string $modulePath): void
    {
        $this->modulePath = $modulePath;
    }

    private function getModulePath(): ?string
    {
        return $this->modulePath;
    }

    /**
     * @Given I have no Magento module called :moduleName
     */
    public function iHaveNoMagentoModuleCalledX(string $moduleName): void
    {
        [$vendor, $module] = explode('_', $moduleName);
        $modulePath = sprintf('%s/app/code/%s/%s', $this->getMagentoRootDirectory(), $vendor, $module);

        $this->filesystem->remove($modulePath);
        Assert::false($this->filesystem->exists($modulePath));
    }

    /**
     * @Given I have a Magento module called :moduleName
     */
    public function iHaveAMagentoModuleCalled(string $moduleName): void
    {
        [$vendor, $module] = explode('_', $moduleName);
        $modulePath = sprintf('%s/app/code/%s/%s', $this->getMagentoRootDirectory(), $vendor, $module);
        $registrationFile = sprintf('%s/registration.php', $modulePath);
        $moduleFile = sprintf('%s/etc/module.xml', $modulePath);
        $this->setModulePath($modulePath);

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

        $this->createFile($registrationFile, $registrationFileContent);
        $this->createFile($moduleFile, $moduleFileContent);
        //$this->runMagentoCommand('module:enable', $moduleName);
    }

    /**
     * @Given I have an interface :fqcn defined in this module:
     * @Given I have a class :fqcn defined in this module:
     */
    public function iHaveAnInterfaceDefinedInThisModule(string $fqcn, PyStringNode $content): void
    {
        $file = sprintf('%s/app/code/%s.php', $this->getMagentoRootDirectory(), str_replace('\\', '/', $fqcn));
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given I have a global Magento DI configuration in this module:
     */
    public function iHaveAGlobalMagentoDiConfigurationInThisModule(PyStringNode $content): void
    {
        $file = sprintf('%s/etc/di.xml', $this->getModulePath());
        $this->createFile($file, $content->getRaw());

        $this->runMagentoCommand('cache:clean');
    }

    /**
     * @Given I have a frontend Magento DI configuration in this module:
     */
    public function iHaveAFrontendMagentoDIConfigurationInThisModule(PyStringNode $content): void
    {
        $file = sprintf('%s/etc/frontend/di.xml', $this->getModulePath());
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given I have an adminhtml Magento DI configuration in this module:
     */
    public function iHaveAnAdminhtmlMagentoDIConfigurationInThisModule(PyStringNode $content): void
    {
        $file = sprintf('%s/etc/adminhtml/di.xml', $this->modulePath);
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given I have a test Magento DI configuration in this module:
     */
    public function iHaveATestMagentoDiConfigurationInThisModule(PyStringNode $content): void
    {
        $file = sprintf('%s/etc/test/di.xml', $this->modulePath);
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given I have the helper service configuration:
     */
    public function iHaveTheHelperServiceConfiguration(PyStringNode $content): void
    {
        $file = sprintf('%s/features/bootstrap/config/services.yml', $this->getWorkingDirectory());
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given /^the behat helper service class file "([^"]*)" contains:$/
     */
    public function theBehatHelperServiceClassFileContains(string $className, PyStringNode $content): void
    {
        $file = sprintf(
            '%s/features/bootstrap/%s.php',
            $this->getWorkingDirectory(),
            str_replace('\\', '/', $className),
        );
        $this->createFile($file, $content->getRaw());
    }

    /**
     * @Given I compile the DI
     */
    public function iCompileTheDi(): void
    {
        $this->iRunTheMagentoCommand('setup:di:compile');
    }

    /**
     * @Given I :clearOrFlush the cache
     */
    public function iCleanOrFlushTheCache(string $cleanOrFlush): void
    {
        Assert::inArray($cleanOrFlush, ['clean', 'flush'], 'Can only clean or flush the cache');
        $this->iRunTheMagentoCommand(sprintf('cache:%s', $cleanOrFlush));
    }

    /**
     * @Given I run the Magento command :command
     * @Given I run the Magento command :command with arguments :arguments
     */
    public function iRunTheMagentoCommand(string $command, ?string $arguments = null): void
    {
        $this->runMagentoCommand($command, $arguments);
    }

    protected function runMagentoCommand(?string $command = null, ?string $arguments = null): void
    {
        $magentoProcess = $this->processFactory->createFromInput(
            new MagentoCommandInput($command, $arguments, $this->getMagentoRootDirectory()),
        );

        $this->addProcess($magentoProcess);
        $magentoProcess->setTimeout(600);
        $magentoProcess->run();
        Assert::same(
            $magentoProcess->getExitCode(),
            0,
            sprintf(
                'Expected Exit Code of Magento Process to be 0, got %d with message %s',
                $magentoProcess->getExitCode(),
                $magentoProcess->getErrorOutput(),
            ),
        );
    }

    private function getMagentoRootDirectory(): string
    {
        if ($this->magentoRootDirectory === null) {
            $this->magentoRootDirectory = $this->magentoPathProvider->getMagentoRootDirectory();
        }

        return $this->magentoRootDirectory;
    }

    private function revertMagentoConfig(): void
    {
        if ($this->filesystem->exists('/tmp/config.php.backup')) {
            $this->filesystem->copy(
                '/tmp/config.php.backup',
                sprintf('%s/app/etc/config.php', $this->getMagentoRootDirectory()),
                true,
            );
            $this->filesystem->remove('/tmp/config.php.backup');
            $this->cacheCleaner->clean(false);
        }
    }

    private function removeModuleFolders(): void
    {
        $modulePath = $this->getModulePath();
        if ($modulePath && $this->filesystem->exists($modulePath)) {
            $this->filesystem->remove($modulePath);
        }
    }

    private function determineFreshWorkingDirectoryFlag(): void
    {
        $directory = $this->getWorkingDirectory();
        Assert::string($directory, 'Working directory is not a string');
        $this->isFreshWorkingDirectory = $this->filesystem->exists($directory) === false;
    }

    private function removeEmptyWorkingDirectory(): void
    {
        $directory = $this->getWorkingDirectory();
        Assert::string($directory, 'Working directory is not a string');
        if ($this->isFreshWorkingDirectory === true) {
            $this->filesystem->remove($directory);
        }
    }
}
