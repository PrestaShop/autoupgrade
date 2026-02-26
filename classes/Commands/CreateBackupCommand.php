<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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
            ->addOption('max-file-size', null, InputOption::VALUE_REQUIRED, 'Max file size allowed in backup')
            ->addOption('max-sql-size', null, InputOption::VALUE_REQUIRED, 'Reference for SQL file creation, giving a file size before another request is needed')
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
                BackupConfiguration::MAX_FILE_SIZE => 'max-file-size',
                BackupConfiguration::MAX_SQL_SIZE_TO_WRITE_PER_CALL => 'max-sql-size',
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
