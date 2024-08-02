<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Commands;

use PrestaShop\Module\AutoUpgrade\Services\DistributionApiService;
use PrestaShop\Module\AutoUpgrade\UpgradeContainer;
use PrestaShop\Module\AutoUpgrade\UpgradeSelfCheck;
use PrestaShop\Module\AutoUpgrade\Task\ExitCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class CheckRequirementsCommand extends Command
{
    protected static $defaultName = 'upgrade:check-requirements';
    const MODULE_CONFIG_DIR = 'autoupgrade';

    protected function configure()
    {
        $this
            ->setDescription('Check all prerequisites for an upgrade.')
            ->setHelp('This command allows you to check the prerequisites necessary for the proper functioning of an upgrade.')
            ->addArgument('admin-dir', InputArgument::REQUIRED, 'The admin directory name.');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $successStyle = new OutputFormatterStyle('green', null, ['bold']);
        $errorStyle = new OutputFormatterStyle('red', null, ['bold']);
        $warningStyle = new OutputFormatterStyle('yellow', null, ['bold']);
        $output->getFormatter()->setStyle('success', $successStyle);
        $output->getFormatter()->setStyle('error', $errorStyle);
        $output->getFormatter()->setStyle('warning', $warningStyle);

        $prodRootDir = _PS_ROOT_DIR_;
        $adminDir = realpath($input->getArgument('admin-dir'));
        $moduleConfigPath = $adminDir . DIRECTORY_SEPARATOR . self::MODULE_CONFIG_DIR;

        $upgradeContainer = new UpgradeContainer($prodRootDir, $adminDir);
        $upgradeContainer->initPrestaShopAutoloader();
        $upgradeContainer->initPrestaShopCore();

        $selfCheck = new UpgradeSelfCheck(
            $upgradeContainer->getUpgrader(),
            $upgradeContainer->getPrestaShopConfiguration(),
            new DistributionApiService(),
            $prodRootDir,
            $adminDir,
            $moduleConfigPath
        );

        if (!$selfCheck->isModuleVersionLatest()) {
            $output->writeln('<warning>⚠</warning> Your current version of the module is out of date.');
        }

        $results = $this->getRequirementsResult($selfCheck);

        if (!$selfCheck->isOkForUpgrade()) {
            $table = new Table($output);
            $table
                ->setHeaders(['Requirements', 'Result'])
                ->setRows($results);
            $table->render();

            return ExitCode::FAIL;
        } else {
            $output->writeln('<success>✔</success> All prerequisites meet the conditions for an update.');

            return ExitCode::SUCCESS;
        }
    }

    /**
     * @return array<array<string, string>>
     */
    protected function getRequirementsResult(UpgradeSelfCheck $selfCheck): array
    {
        $requirements = [
            'isShopVersionMatchingVersionInDatabase' => $selfCheck->isShopVersionMatchingVersionInDatabase(),
            'rootDirectoryIsWritable' => $selfCheck->isRootDirectoryWritable(),
            'adminDirectoryIsWritable' => $selfCheck->isAdminAutoUpgradeDirectoryWritable(),
            'adminDirectoryWritableReport' => $selfCheck->getAdminAutoUpgradeDirectoryWritableReport(),
            'safeModeIsDisabled' => $selfCheck->isSafeModeDisabled(),
            'allowUrlFopenOrCurlIsEnabled' => $selfCheck->isFOpenOrCurlEnabled(),
            'zipIsEnabled' => $selfCheck->isZipEnabled(),
            'storeIsInMaintenance' => $selfCheck->isShopDeactivated(),
            'isLocalEnvironment' => $selfCheck->isLocalEnvironment(),
            'cachingIsDisabled' => $selfCheck->isCacheDisabled(),
            'maxExecutionTime' => $selfCheck->getMaxExecutionTime(),
            'checkApacheModRewrite' => $selfCheck->isApacheModRewriteEnabled(),
            'notLoadedPhpExtensions' => $selfCheck->getNotLoadedPhpExtensions(),
            'checkKeyGeneration' => $selfCheck->checkKeyGeneration(),
            'checkMemoryLimit' => $selfCheck->isMemoryLimitValid(),
            'checkFileUploads' => $selfCheck->isPhpFileUploadsConfigurationEnabled(),
            'notExistsPhpFunctions' => $selfCheck->getNotExistsPhpFunctions(),
            'checkPhpSessions' => $selfCheck->isPhpSessionsValid(),
            'missingFiles' => $selfCheck->getMissingFiles(),
            'notWritingDirectories' => $selfCheck->getNotWritingDirectories(),
        ];

        $results = [];

        foreach ($requirements as $key => $value) {
            if ($value === false || (is_array($value) && !empty($value))) {
                $results[] = [$key, '<error>✘</error>'];
            }
        }

        $phpRequirementsState = $selfCheck->getPhpRequirementsState();

        if ($phpRequirementsState != UpgradeSelfCheck::PHP_REQUIREMENTS_VALID) {
            if ($phpRequirementsState == UpgradeSelfCheck::PHP_REQUIREMENTS_INVALID) {
                $results[] = ['phpRequirementsState', '<error>✘</error>'];
            } else {
                $results[] = ['phpRequirementsState', '<warning>⚠</warning>'];
            }
        }

        return $results;
    }
}