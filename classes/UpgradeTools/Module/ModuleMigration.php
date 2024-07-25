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

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

use LogicException;
use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use Throwable;

class ModuleMigration
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var string|null */
    private $moduleName;

    /** @var string|null */
    private $dbVersion;

    /** @var string|null */
    private $localVersion;

    /** @var string[]|null */
    private $migrationFiles;

    /** @var string|null */
    private $upgradeFilesRootPath;

    /** @var \Module|null */
    private $moduleInstance;

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->moduleName = null;
        $this->dbVersion = null;
        $this->localVersion = null;
        $this->migrationFiles = null;
        $this->upgradeFilesRootPath = null;
        $this->moduleInstance = null;
    }

    public function setMigrationContext(\Module $moduleInstance, ?string $dbVersion): void
    {
        $this->moduleInstance = $moduleInstance;

        $moduleName = $moduleInstance->name;

        $this->moduleName = $moduleName;
        $this->upgradeFilesRootPath = _PS_MODULE_DIR_ . $moduleName . DIRECTORY_SEPARATOR . 'upgrade';

        $this->localVersion = $this->moduleInstance->version;
        $this->dbVersion = $dbVersion ?? '0';

        if ($this->dbVersion === '0') {
            $this->logger->notice($this->translator->trans('No version present in database for module %s, all files for upgrade will be applied.', [$this->moduleName]));
        }
    }

    public function needMigration(): bool
    {
        if (version_compare($this->localVersion, $this->dbVersion, '>')) {
            if (empty($this->migrationFiles)) {
                $this->migrationFiles = $this->listUpgradeFiles();
            }

            return !empty($this->migrationFiles);
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function listUpgradeFiles(): array
    {
        $files = glob($this->upgradeFilesRootPath . '/*.php', GLOB_BRACE);

        $upgradeFiles = [];

        foreach ($files as $file) {
            if (preg_match('/upgrade-(\d+(?:\.\d+){0,2}).php$/', basename($file), $matches)) {
                $fileVersion = $matches[1];
                if (version_compare($fileVersion, $this->dbVersion, '>') && version_compare($fileVersion, $this->localVersion, '<=')) {
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
    public function runMigration(): void
    {
        if (!$this->moduleInstance || !$this->moduleName || $this->localVersion === null || $this->dbVersion === null) {
            throw (new LogicException('Module migration context is empty, please run setMigrationContext() first.'));
        }

        if ($this->migrationFiles === null) {
            throw (new LogicException('Module upgrade files are empty, please run needMigration() first.'));
        }

        foreach ($this->migrationFiles as $index => $migrationFilePath) {
            $this->logger->notice($this->translator->trans('(%s/%s) Applying migration file %s.', [($index + 1), count($this->migrationFiles), basename($migrationFilePath)]));

            $methodName = $this->getUpgradeMethodName($migrationFilePath);

            // check if function already exists to prevent upgrade crash
            if (function_exists($methodName)) {
                $this->moduleInstance->disable();
                throw (new UpgradeException($this->translator->trans('[WARNING] Method %s already exists. Migration for module %s aborted, you can try again later on the module manager. Module %s disabled.', [$methodName, $this->moduleName, $this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            include $migrationFilePath;

            // @phpstan-ignore booleanNot.alwaysTrue (we ignore this error because we load a file with methods)
            if (!function_exists($methodName)) {
                $this->moduleInstance->disable();
                throw (new UpgradeException($this->translator->trans('[WARNING] Method %s does not exist. Module %s disabled.', [$methodName, $this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            // @phpstan-ignore deadCode.unreachable (we ignore this error because the previous if can be true or false)
            try {
                if (!$methodName($this->moduleInstance)) {
                    throw (new UpgradeException($this->translator->trans('[WARNING] Migration failed while running the file %s. Module %s disabled.', [basename($migrationFilePath), $this->moduleName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
                }
            } catch (UpgradeException $e) {
                $this->moduleInstance->disable();
                throw $e;
            } catch (Throwable $t) {
                $this->moduleInstance->disable();
                throw (new UpgradeException($this->translator->trans('[WARNING] Unexpected error when trying to upgrade module %s. Module %s disabled.', [$this->moduleName, $this->moduleName]), 0, $t))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }
        }
    }

    private function getUpgradeMethodName(string $filePath): string
    {
        $fileName = basename($filePath);

        preg_match('/upgrade-([\d.]+)\.php$/', $fileName, $matches);

        $version = str_replace('.', '_', $matches[1]);

        return 'upgrade_module_' . $version;
    }
}
