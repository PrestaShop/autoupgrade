<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use Exception;
use InvalidArgumentException;
use PrestaShop\Module\AutoUpgrade\DocumentationLinks;
use PrestaShop\Module\AutoUpgrade\Parameters\RestoreConfiguration;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use PrestaShop\Module\AutoUpgrade\Task\Runner\AllRestoreTasks;
use PrestaShop\Module\AutoUpgrade\VersionUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends AbstractBackupCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'backup:restore';

    protected function configure(): void
    {
        $this
            ->setDescription('Restore the store to a previous state from a backup file.')
            ->setHelp(
                'This command allows you to restore the store to a previous state from a backup file.' .
                'See https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/upgrade-module/upgrade-cli/#rollback-cli for more details'
            )
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.')
            ->addOption('config-file-path', null, InputOption::VALUE_REQUIRED, 'Configuration file location.')
            ->addOption('backup', null, InputOption::VALUE_REQUIRED, 'Specify the backup name to restore (this can be found in your folder <admin directory>/autoupgrade/backup/)')
            ->addOption('max-seconds-per-batch', null, InputOption::VALUE_REQUIRED, 'Number of seconds allowed before having to make another request');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->setupEnvironment($input, $output);
            $configPath = $input->getOption('config-file-path');
            $backup = $input->getOption('backup');

            if (!$backup) {
                if (!$input->isInteractive()) {
                    throw new InvalidArgumentException("The '--backup' option is required.");
                }

                $backup = $this->selectBackupInteractive($input, $output);

                if (!$backup) {
                    return ExitCode::SUCCESS;
                }
            }

            $this->consoleInputConfiguration[RestoreConfiguration::BACKUP_NAME] = $backup;

            $secondsPerCall = $input->getOption('max-seconds-per-batch');
            if ($secondsPerCall) {
                $this->consoleInputConfiguration[RestoreConfiguration::MAX_SECONDS_PER_BATCH] = $secondsPerCall;
            }

            $loader = $this->upgradeContainer->getRestoreConfigurationLoader();
            $this->loadConfiguration($loader, $configPath);

            $controller = new AllRestoreTasks($this->upgradeContainer);
            $controller->init();
            $exitCode = $controller->run();

            if ($exitCode === ExitCode::SUCCESS) {
                $this->printPostProcessChecklist($output);
            }

            $this->logger->debug('Process completed with exit code: ' . $exitCode);

            return $exitCode;
        } catch (Exception $e) {
            $this->logger->error("An error occurred during the restoration process:\n" . $e);
            throw $e;
        }
    }

    private function printPostProcessChecklist(OutputInterface $output): void
    {
        $version = $this->upgradeContainer->getPrestaShopConfiguration()->getPrestaShopVersion();
        $major = VersionUtils::splitPrestaShopVersion($version)['major'];

        $output->writeln('We recommend you to visit the Post-restore checklist page in the developer documentation for any further help: ' . DocumentationLinks::getDevDocUpdateAssistantPostRestoreUrl($major));
    }
}
