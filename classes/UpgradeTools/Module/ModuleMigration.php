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

use PrestaShop\Module\AutoUpgrade\Exceptions\UpgradeException;
use PrestaShop\Module\AutoUpgrade\Log\Logger;
use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;
use LogicException;

class ModuleMigration
{
    /** @var Translator */
    private $translator;

    /** @var Logger */
    private $logger;

    /** @var string|null */
    private $module_name;

    /** @var string|null */
    private $db_version;

    /** @var string|null */
    private $local_version;

    /** @var string[]|null */
    private $migration_files;

    /** @var string|null */
    private $upgrade_files_root_path;

    /** @var \Module|null */
    private $module_instance;

    public function __construct(Translator $translator, Logger $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->module_name = null;
        $this->db_version = null;
        $this->local_version = null;
        $this->migration_files = null;
        $this->upgrade_files_root_path = null;
        $this->module_instance = null;
    }

    public function setMigrationContext(\Module $module_instance, ?string $db_version): void
    {
        $this->module_instance = $module_instance;

        $moduleName = $module_instance->name;

        $this->module_name = $moduleName;
        $this->upgrade_files_root_path = _PS_MODULE_DIR_ . $moduleName . DIRECTORY_SEPARATOR . 'upgrade';

        $this->local_version = $this->module_instance->version;
        $this->db_version = $db_version ?? '0';

        if ($this->db_version === '0') {
            $this->logger->notice($this->translator->trans('No database version provided for module %s, all files for upgrade will be applied.', [$this->module_name]));
        }
    }

    public function needMigration(): bool
    {
        if (version_compare($this->local_version, $this->db_version, '>')) {
            if (empty($this->migration_files)) {
                $this->migration_files = $this->listUpgradeFiles();
            }

            return !empty($this->migration_files);
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function listUpgradeFiles(): array
    {
        $files = glob($this->upgrade_files_root_path . '/*.php', GLOB_BRACE);

        $upgradeFiles = [];

        foreach ($files as $file) {
            if (preg_match('/upgrade-(\d+(?:\.\d+){0,2}).php$/', basename($file), $matches)) {
                $fileVersion = $matches[1];
                if (version_compare($fileVersion, $this->db_version, '>') && version_compare($fileVersion, $this->local_version, '<=')) {
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
        if (!$this->module_instance || !$this->module_name || !$this->local_version || !$this->db_version) {
            throw (new LogicException('Module migration context is empty, please run setMigrationContext() first.'));
        }

        if ($this->migration_files === null) {
            throw (new LogicException('Module upgrade files are empty, please run needMigration() first.'));
        }

        foreach ($this->migration_files as $index => $migrationFilePath) {
            $this->logger->notice($this->translator->trans('(%s/%s) Applying migration file %s.', [($index + 1), count($this->migration_files), baseName($migrationFilePath)]));

            $methodName = $this->getUpgradeMethodName($migrationFilePath);

            // check if function already exist to prevent upgrade crash
            if (function_exists($methodName)) {
                throw (new UpgradeException($this->translator->trans('[WARNING] Method %s already exists. Migration for module %s aborted, you can try again later on the module manager.', [$methodName, $this->module_name])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            include $migrationFilePath;

            if (!function_exists($methodName)) {
                throw (new UpgradeException($this->translator->trans('[WARNING] Method %s does not exist', [$methodName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }

            if (!$methodName($this->module_instance)) {
                $this->module_instance->disable();
                throw (new UpgradeException($this->translator->trans('[WARNING] The method %s encountered an issue during migration.', [$methodName])))->setSeverity(UpgradeException::SEVERITY_WARNING);
            }
        }
    }

    private function getUpgradeMethodName($filePath): string
    {
        $fileName = basename($filePath);

        preg_match('/upgrade-([\d.]+)\.php$/', $fileName, $matches);

        $version = str_replace('.', '_', $matches[1]);

        return 'upgrade_module_' . $version;
    }
}
