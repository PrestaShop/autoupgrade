<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListBackupCommand extends AbstractBackupCommand
{
    /** @var string */
    protected static $defaultName = 'backup:list';

    protected function configure(): void
    {
        $this
            ->setDescription('List all available backups.')
            ->setHelp('This command list all available backups for the store.')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);

            $backups = $this->backupFinder->getSortedAndFormatedAvailableBackups();

            if (empty($backups)) {
                $this->logger->info('No store backup files found in your dedicated directory');

                return ExitCode::SUCCESS;
            }

            $rows = $this->getRows($backups);
            $table = new Table($output);
            $table
                ->setHeaders(['Date', 'Version', 'File name'])
                ->setRows($rows)
            ;
            $table->render();

            return ExitCode::SUCCESS;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the backup listing process:\n" . $e);
            throw $e;
        }
    }

    /**
     * @param array<array{timestamp: int, datetime: string, version:string, filename: string}> $backups
     *
     * @return array<int, array{datetime: string, version:string, filename: string}>
     */
    private function getRows(array $backups): array
    {
        $rows = [];
        foreach ($backups as $row) {
            $rows[] = [
                'datetime' => $row['datetime'],
                'version' => $row['version'],
                'filename' => $row['filename'],
            ];
        }

        return $rows;
    }
}
