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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

class ModuleMigration
{
    /** @var Filesystem */
    private $filesystem;

    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    public function __construct(Filesystem $filesystem, Translator $translator, Logger $logger)
    {
        $this->filesystem = $filesystem;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function needMigration(ModuleMigrationContext $moduleMigrationContext): bool
    {
        if (version_compare($moduleMigrationContext->getLocalVersion(), $moduleMigrationContext->getDbVersion(), '>')) {
            if (empty($moduleMigrationContext->getMigrationFiles())) {
                $migrationFiles = $this->listUpgradeFiles($moduleMigrationContext);
                $moduleMigrationContext->setMigrationFiles($migrationFiles);
            }

            return !empty($moduleMigrationContext->getMigrationFiles());
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function listUpgradeFiles(ModuleMigrationContext $moduleMigrationContext): array
    {
        if ($moduleMigrationContext->getDbVersion() === '0') {
            $this->logger->notice($this->translator->trans('No version present in database for module %s, all files for update will be applied.', [$moduleMigrationContext->getModuleName()]));
        }

        $files = glob($moduleMigrationContext->getUpgradeFilesRootPath() . '/*.php');

        $upgradeFiles = [];

        foreach ($files as $file) {
            if (preg_match('/(?:install|upgrade)-(\d+(?:\.\d+){0,2}).php$/', basename($file), $matches)) {
                $fileVersion = $matches[1];
                if (version_compare($fileVersion, $moduleMigrationContext->getDbVersion(), '>') && version_compare($fileVersion, $moduleMigrationContext->getLocalVersion(), '<=')) {
                    $upgradeFiles[] = ['file' => $file, 'version' => $fileVersion];
                }
            }
        }

        usort($upgradeFiles, function ($a, $b) {
            return version_compare($a['version'], $b['version']);
        });

        return array_column($upgradeFiles, 'file');
    }

    /**
     * @throws LogicException
     * @throws UpgradeException
     */
    public function runMigration(ModuleMigrationContext $moduleMigrationContext): void
    {
        if ($moduleMigrationContext->getMigrationFiles() === null) {
            throw (new LogicException('Module upgrade files are empty, please run needMigration() first.'));
        }

        foreach ($moduleMigrationContext->getMigrationFiles() as $index => $migrationFilePath) {
            $this->logger->notice($this->translator->trans('(%s/%s) Applying migration file %s.', [($index + 1), count($moduleMigrationContext->getMigrationFiles()), basename($migrationFilePath)]));

            $methodName = $this->getUpgradeMethodName($migrationFilePath);

            try {
                if (!$this->loadAndCallFunction($migrationFilePath, $methodName, $moduleMigrationContext)) {
                    throw (new UpgradeException($this->translator->trans('Migration failed while running the file %s. Module %s disabled.', [basename($migrationFilePath), $moduleMigrationContext->getModuleName()])))->setSeverity(UpgradeException::SEVERITY_WARNING);
                }
            } catch (UpgradeException $e) {
                $moduleMigrationContext->getModuleInstance()->disable();
                throw $e;
            } catch (Throwable $t) {
                $moduleMigrationContext->getModuleInstance()->disable();
                throw (new UpgradeException($this->translator->trans('Unexpected issue when trying to upgrade module %s. Module %s disabled.', [$moduleMigrationContext->getModuleName(), $moduleMigrationContext->getModuleName()]), 0, $t))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }
        }
    }

    /**
     * @throws UpgradeException
     */
    public function saveVersionInDb(ModuleMigrationContext $moduleMigrationContext): void
    {
        if (!\Module::upgradeModuleVersion($moduleMigrationContext->getModuleName(), $moduleMigrationContext->getLocalVersion())) {
            throw (new UpgradeException($this->translator->trans('Module %s version could not be updated. Database might be unavailable.', [$moduleMigrationContext->getModuleName()]), 0))->setSeverity(UpgradeException::SEVERITY_WARNING);
        }
    }

    private function getUpgradeMethodName(string $filePath): string
    {
        $fileName = basename($filePath);

        preg_match('/(?:install|upgrade)-([\d.]+)\.php$/', $fileName, $matches);

        $version = str_replace('.', '_', $matches[1]);

        return 'upgrade_module_' . $version;
    }

    /**
     * Loads the migration file and calls the specified method using eval.
     *
     * @throws UpgradeException
     */
    private function loadAndCallFunction(string $filePath, string $methodName, ModuleMigrationContext $moduleMigrationContext): bool
    {
        $uniqueMethodName = $moduleMigrationContext->getModuleName() . '_' . $methodName;

        $updateDirectory = dirname($filePath);
        $sandboxedFilePath = $updateDirectory . DIRECTORY_SEPARATOR . $uniqueMethodName . '.php';

        try {
            $this->filesystem->dumpFile($sandboxedFilePath, str_replace($methodName, $uniqueMethodName, file_get_contents($filePath)));

            require_once $sandboxedFilePath;
            if (!function_exists($uniqueMethodName)) {
                throw (new UpgradeException($this->translator->trans('Method %s does not exist. Module %s disabled.', [$uniqueMethodName, $moduleMigrationContext->getModuleName()])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            return call_user_func($uniqueMethodName, $moduleMigrationContext->getModuleInstance());
        } catch (IOException $e) {
            throw (new UpgradeException($this->translator->trans('Could not write temporary file %s.', [$sandboxedFilePath])))->setSeverity(UpgradeException::SEVERITY_WARNING);
        } finally {
            $this->filesystem->remove($sandboxedFilePath);
        }
    }
}
