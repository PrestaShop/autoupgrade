<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteBackupCommand extends AbstractBackupCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'backup:delete';

    protected function configure(): void
    {
        $this
            ->setDescription('Delete a store backup file.')
            ->setHelp(
                'This command allows you to delete a store backup file.'
            )
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.')
            ->addOption('backup', null, InputOption::VALUE_REQUIRED, 'Specify the backup name to delete. The allowed values can be found with backup:list command)');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);

            $backup = $input->getOption('backup');
            $exitCode = ExitCode::SUCCESS;

            if (!$backup) {
                if (!$input->isInteractive()) {
                    throw new InvalidArgumentException("The '--backup' option is required.");
                }

                $backup = $this->selectBackupInteractive($input, $output);

                if (!$backup) {
                    return $exitCode;
                }
            }

            $this->backupManager->deleteBackup($backup);
            $this->logger->info('The backup file has been successfully deleted');

            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            return $exitCode;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the delete backup process:\n" . $e);
            throw $e;
        }
    }
}
