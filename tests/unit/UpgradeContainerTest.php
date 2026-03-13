<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\AutoUpgrade\State\BackupState;
use PrestaShop\Module\AutoUpgrade\State\RestoreState;
use PrestaShop\Module\AutoUpgrade\State\UpdateState;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupComplete;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupFiles;
use PrestaShop\Module\AutoUpgrade\Task\Backup\BackupInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreComplete;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreEmpty;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreFiles;
use PrestaShop\Module\AutoUpgrade\Task\Restore\RestoreInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\CleanDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\Download;
use PrestaShop\Module\AutoUpgrade\Task\Update\Unzip;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateComplete;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateDatabase;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateFiles;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateInitialization;
use PrestaShop\Module\AutoUpgrade\Task\Update\UpdateModules;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;

class UpgradeContainerTest extends TestCase
{
    public function testSameResultFormAdminSubDir()
    {
        $container = new UpgradeContainer(__DIR__, __DIR__ . '/..');
        $this->assertNotSame($container->getProperty(UpgradeContainer::PS_ADMIN_SUBDIR), str_replace($container->getProperty(UpgradeContainer::PS_ROOT_PATH), '', $container->getProperty(UpgradeContainer::PS_ADMIN_PATH)));
    }

    /**
     * @dataProvider objectsToInstanciateProvider
     */
    public function testObjectInstanciation($functionName, $expectedClass)
    {
        $container = $this->getMockBuilder(UpgradeContainer::class)
            ->setConstructorArgs([__DIR__, __DIR__ . '/..'])
            ->setMethods(['getDb', 'getUpgrader'])
            ->getMock();

        $container->getUpdateState()->setDestinationVersion('1.7.1.0');
        $actualClass = get_class(call_user_func([$container, $functionName]));
        $this->assertSame($actualClass, $expectedClass);
    }

    public function objectsToInstanciateProvider()
    {
        // | Function to call | Expected class |
        return [
            ['getCacheCleaner', PrestaShop\Module\AutoUpgrade\UpgradeTools\CacheCleaner::class],
            ['getChecksumCompare', PrestaShop\Module\AutoUpgrade\Xml\ChecksumCompare::class],
            ['getCookie', PrestaShop\Module\AutoUpgrade\Cookie::class],
            ['getFileStorage', PrestaShop\Module\AutoUpgrade\Parameters\FileStorage::class],
            ['getFileFilter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\FileFilter::class],
            // ['getUpgrader', \PrestaShop\Module\AutoUpgrade\Upgrader::class],
            ['getFilesystemAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\FilesystemAdapter::class],
            ['getFileLoader', PrestaShop\Module\AutoUpgrade\Xml\FileLoader::class],
            ['getLogger', PrestaShop\Module\AutoUpgrade\Log\WebLogger::class],
            ['getModuleAdapter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Module\ModuleAdapter::class],
            ['getUpdateState', \PrestaShop\Module\AutoUpgrade\State\UpdateState::class],
            ['getSymfonyAdapter', PrestaShop\Module\AutoUpgrade\UpgradeTools\SymfonyAdapter::class],
            ['getTranslationAdapter', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translation::class],
            ['getTranslator', \PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator::class],
            // Cannot be run in the context of unit tests as the class would be loaded from PS dependencies
            // ['getTwig', \Twig\Environment::class],
            ['getPrestaShopConfiguration', PrestaShop\Module\AutoUpgrade\PrestashopConfiguration::class],
            ['getWorkspace', PrestaShop\Module\AutoUpgrade\Workspace::class],
            ['getZipAction', PrestaShop\Module\AutoUpgrade\ZipAction::class],
        ];
    }

    /**
     * @dataProvider stateRelatedToTaskProvider
     */
    public function testRetrievalOfStateWhenGeneratingResponse(string $task, string $expectedStateClass)
    {
        $container = new UpgradeContainer(__DIR__, __DIR__ . '/..');

        $state = $container->getStateFromTaskType($task::TASK_TYPE);
        $this->assertSame($expectedStateClass, get_class($state));
    }

    public function stateRelatedToTaskProvider(): array
    {
        return [
            [BackupComplete::class, BackupState::class],
            [BackupDatabase::class, BackupState::class],
            [BackupFiles::class, BackupState::class],
            [BackupInitialization::class, BackupState::class],

            [RestoreInitialization::class, RestoreState::class],
            [RestoreComplete::class, RestoreState::class],
            [RestoreDatabase::class, RestoreState::class],
            [RestoreEmpty::class, RestoreState::class],
            [RestoreFiles::class, RestoreState::class],

            [CleanDatabase::class, UpdateState::class],
            [Download::class, UpdateState::class],
            [Unzip::class, UpdateState::class],
            [UpdateComplete::class, UpdateState::class],
            [UpdateDatabase::class, UpdateState::class],
            [UpdateFiles::class, UpdateState::class],
            [UpdateInitialization::class, UpdateState::class],
            [UpdateModules::class, UpdateState::class],
        ];
    }
}
