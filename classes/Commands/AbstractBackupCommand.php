<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use PrestaShop\Module\AutoUpgrade\Backup\BackupFinder;
use PrestaShop\Module\AutoUpgrade\Backup\BackupManager;
use PrestaShop\Module\AutoUpgrade\Exceptions\BackupException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class AbstractBackupCommand extends AbstractCommand
{
    /** @var BackupFinder */
    protected $backupFinder;

    /** @var BackupManager */
    protected $backupManager;

    protected function setupEnvironment(InputInterface $input, OutputInterface $output): void
    {
        parent::setupEnvironment($input, $output);
        $this->backupFinder = $this->upgradeContainer->getBackupFinder();
        $this->backupManager = $this->upgradeContainer->getBackupManager();
    }

    /**
     * @throws Exception
     */
    protected function selectBackupInteractive(InputInterface $input, OutputInterface $output): ?string
    {
        $backups = $this->backupFinder->getSortedAndFormatedAvailableBackups();

        if (empty($backups)) {
            $this->logger->info('No store backup files found in your dedicated directory');

            return null;
        }

        $rows = array_map(function ($backup) {
            return $this->formatBackupRow($backup);
        }, $backups);

        $exit = 'Exit the process';
        $rows[] = $exit;

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your backup:',
            $rows
        );

        $answer = $helper->ask($input, $output, $question);

        if ($answer === $exit) {
            return null;
        }

        $key = array_search($answer, $rows);
        if ($key === false) {
            throw new BackupException('Invalid backup selection.');
        }

        return $backups[$key]['filename'];
    }

    /**
     * Formats a backup row for display in the selection prompt.
     *
     * @param array{datetime: string, version:string, filename: string} $backups
     *
     * @return string
     */
    private function formatBackupRow(array $backups): string
    {
        return sprintf('Date: %s, Version: %s, File name: %s', $backups['datetime'], $backups['version'], $backups['filename']);
    }
}
