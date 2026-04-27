<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\Parameters\BackupConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllBackupTasks;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBackupCommand extends AbstractCommand
{
    /** @var string */
    protected static $defaultName = 'backup:create';

    protected function configure(): void
    {
        $this
            ->setDescription('Create backup.')
            ->setHelp('This command triggers the creation of the files and database backup.')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location.')
            ->addOption('include-images', null, InputOption::VALUE_REQUIRED, 'Include, or not, images in the store backup.')
            ->addOption('max-files-per-batch', null, InputOption::VALUE_REQUIRED, 'Number of files to handle in a single call to avoid timeouts')
            ->addOption('max-file-size-allowed', null, InputOption::VALUE_REQUIRED, 'Max file size allowed in backup')
            ->addOption('max-sql-size-to-write-per-batch', null, InputOption::VALUE_REQUIRED, 'Reference for SQL file creation, giving a file size before another request is needed')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);
            $configPath = $input->getOption('config-file-path');

            $this->processConsoleInputConfiguration($input, [
                BackupConfiguration::KEEP_IMAGES => 'include-images',
                BackupConfiguration::MAX_FILES_PER_BATCH => 'max-files-per-batch',
                BackupConfiguration::MAX_FILE_SIZE_ALLOWED => 'max-file-size-allowed',
                BackupConfiguration::MAX_SQL_SIZE_TO_WRITE_PER_BATCH => 'max-sql-size-to-write-per-batch',
            ]);

            $loader = $this->upgradeContainer->getBackupConfigurationLoader();
            $this->loadConfiguration($loader, $configPath);

            $controller = new AllBackupTasks($this->upgradeContainer);
            $controller->init();
            $exitCode = $controller->run();
            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            return $exitCode;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the backup creation process::\n" . $e);
            throw $e;
        }
    }
}
